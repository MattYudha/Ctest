<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Letter;
use App\Models\LetterTemplate;
use App\Models\LetterConfiguration;
use Carbon\Carbon;
use App\Models\OfficeLocation;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Signature;
use App\Models\Presence;
use App\Models\LetterTag;
use App\Constants\Roles;

class LetterController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // switch back to auth facade as per your habit bro
            $user = Auth::user();
            $query = Letter::with('user', 'approver', 'template');

            // determine admin status using roles::isadmin helper
            $isAdmin = false;
            if ($user && $user->employee && $user->employee->role) {
                $isAdmin = Roles::isAdmin($user->employee->role->title);
            }

            // non-admins can only see their own letters
            if (!$isAdmin && $user) {
                $query->where('user_id', $user->id);
            }

            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->addIndexColumn()
                // pass $user and $isadmin into the datatables closure scope
                ->addColumn('action', function ($row) use ($user, $isAdmin) {
                    $btns = '<div class="btn-group btn-group-sm" role="group">';

                    // view details button (always available)
                    $btns .=
                        '<a href="' .
                        route('letters.show', $row->id) .
                        '" class="btn btn-outline-info" title="View Letter"><i class="bi bi-eye"></i></a>';

                    // edit button (only if draft and belongs to user)
                    if ($row->status === 'draft' && $user && $row->user_id == $user->id) {
                        $btns .=
                            '<a href="' .
                            route('letters.edit', $row->id) .
                            '" class="btn btn-outline-warning" title="Edit Letter"><i class="bi bi-pencil"></i></a>';
                    }

                    // delete button (admin free, user only when draft status)
                    if ($isAdmin || ($user && $row->user_id == $user->id && $row->status === 'draft')) {
                        $btns .=
                            '<form action="' .
                            route('letters.destroy', $row->id) .
                            '" method="POST" class="d-inline delete-letter-form">
                                    <input type="hidden" name="_token" value="' .
                            csrf_token() .
                            '">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="btn btn-outline-danger" title="Delete Letter"><i class="bi bi-trash"></i></button>
                                  </form>';
                    }

                    $btns .= '</div>';
                    return $btns;
                })
                ->addColumn('status_badge', function ($row) {
                    $class = match ($row->status) {
                        'approved' => 'bg-success',
                        'pending' => 'bg-info',
                        'rejected' => 'bg-danger',
                        default => 'bg-secondary',
                    };
                    return '<span class="badge ' . $class . '">' . ucfirst($row->status) . '</span>';
                })
                ->editColumn('created_date', function ($row) {
                    return $row->created_date ? Carbon::parse($row->created_date)->format('d M Y') : '-';
                })
                ->rawColumns(['action', 'status_badge'])
                ->make(true);
        }

        return view('letters.index');
    }

    public function create()
    {
        $templates = LetterTemplate::where('is_active', true)->get();
        return view('letters.create', compact('templates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'letter_type' => 'required|in:official,memo,notice',
            'letter_template_id' => 'nullable|exists:letter_templates,id',
            'dynamic_tags' => 'nullable|array', // dynamic input validation
        ]);

        $content = $request->content;

        // process replace dynamic tags if any
        if ($request->has('dynamic_tags')) {
            foreach ($request->dynamic_tags as $tagName => $value) {
                $displayValue = $value; // default value to be replaced into text

                // check this tag type from database
                $tagData = LetterTag::where('tag_name', $tagName)->first();

                if ($tagData) {
                    // if the type is date, change to localized format
                    if ($tagData->input_type === 'date' && !empty($value)) {
                        $displayValue = \Carbon\Carbon::parse($value)->locale('id')->translatedFormat('d F Y');
                    }

                    // if the type is a dropdown from the database, get the original name so the number "3" doesn't appear in the letter
                    if ($tagData->input_type === 'dropdown' && $tagData->dropdown_type === 'model') {
                        if ($tagData->dropdown_model === 'OfficeLocation') {
                            $displayValue = \App\Models\OfficeLocation::find($value)?->name ?? $value;
                        } elseif ($tagData->dropdown_model === 'Employee') {
                            // get user name based on user id
                            $displayValue = \App\Models\User::find($value)?->name ?? $value;
                        } elseif ($tagData->dropdown_model === 'Position') {
                            $displayValue = \App\Models\Position::find($value)?->position_name ?? $value;
                        }
                    }

                    // if the type is currency, format it to Rp X.XXX.XXX
                    if ($tagData->input_type === 'currency' && !empty($value)) {
                        $displayValue = 'Rp' . number_format((float)$value, 0, ',', '.');
                    }

                    // if the type is terbilang, read the referenced value and convert it
                    if ($tagData->input_type === 'terbilang' && !empty($value)) {
                        $angka = $request->dynamic_tags[$value] ?? 0;
                        $displayValue = \App\Helpers\Terbilang::make($angka);
                    }
                }

                // replace text inside the letter content
                $content = str_replace('[' . $tagName . ']', $displayValue, $content);
            }
        }

        Letter::create([
            'user_id' => Auth::id(),
            'subject' => $request->subject,
            'content' => $content, // save the finalized content
            'meta_data' => $request->has('dynamic_tags') ? json_encode($request->dynamic_tags) : null,
            'letter_type' => $request->letter_type,
            'letter_template_id' => $request->letter_template_id,
            'status' => 'draft',
            'created_date' => now(),
        ]);

        return redirect()->route('letters.index')->with('success', 'Letter created successfully.');
    }

    public function show(Letter $letter)
    {
        $user = Auth::user();

        // check normal access rights (document creator or hr/admin)
        $isOwner = $letter->user_id === $user->id;
        $isAdmin = $user->employee && in_array($user->employee->role->title, ['HR Administrator', Roles::MASTER_ADMIN]);

        // check vip pass: is this user on the list of people who must sign this document?
        // we check in the signature model, is there a signature request for this user in this letter
        $isSigner = Signature::where('signable_type', Letter::class)
            ->where('signable_id', $letter->id)
            ->where('user_id', $user->id)
            ->exists();

        // 3. execute block: kick out if not owner, not admin, and not a signer
        if (!$isOwner && !$isAdmin && !$isSigner) {
            abort(
                403,
                'Unauthorized access to letter. You do not have access rights or have not been assigned to sign this document.',
            );
        }

        return view('letters.show', compact('letter'));
    }

    public function edit(Letter $letter)
    {
        if (!in_array($letter->status, ['draft', 'pending'])) {
            return redirect()->route('letters.index')->with('error', 'Only Draft and Pending letters can be edited.');
        }

        if ($letter->user_id !== Auth::id()) {
            return redirect()->route('letters.index')->with('error', 'You cannot edit this letter.');
        }

        $templates = LetterTemplate::where('is_active', true)->get();
        return view('letters.edit', compact('letter', 'templates'));
    }

    public function update(Request $request, Letter $letter)
    {
        if (!in_array($letter->status, ['draft', 'pending'])) {
            return redirect()->route('letters.index')->with('error', 'Only Draft and Pending letters can be edited.');
        }

        if ($letter->user_id !== Auth::id()) {
            return redirect()->route('letters.index')->with('error', 'You cannot edit this letter.');
        }

        $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'letter_type' => 'required|in:official,memo,notice',
            'letter_template_id' => 'nullable|exists:letter_templates,id',
            'dynamic_tags' => 'nullable|array',
        ]);

        $content = $request->content;

        // process replacing dynamic tags if the template is changed/refilled
        if ($request->has('dynamic_tags')) {
            foreach ($request->dynamic_tags as $tagName => $value) {
                $displayValue = $value; // default value to be replaced into text

                // check this tag type from database
                $tagData = LetterTag::where('tag_name', $tagName)->first();

                if ($tagData) {
                    // if the type is date, change to localized format
                    if ($tagData->input_type === 'date' && !empty($value)) {
                        $displayValue = \Carbon\Carbon::parse($value)->locale('id')->translatedFormat('d F Y');
                    }

                    // if the type is a dropdown from the database, get the original name so the number "3" doesn't appear in the letter
                    if ($tagData->input_type === 'dropdown' && $tagData->dropdown_type === 'model') {
                        if ($tagData->dropdown_model === 'OfficeLocation') {
                            $displayValue = \App\Models\OfficeLocation::find($value)?->name ?? $value;
                        } elseif ($tagData->dropdown_model === 'Employee') {
                            // get user name based on user id
                            $displayValue = \App\Models\User::find($value)?->name ?? $value;
                        } elseif ($tagData->dropdown_model === 'Position') {
                            $displayValue = \App\Models\Position::find($value)?->position_name ?? $value;
                        }
                    }

                    // if the type is currency, format it to Rp X.XXX.XXX
                    if ($tagData->input_type === 'currency' && !empty($value)) {
                        $displayValue = 'Rp' . number_format((float)$value, 0, ',', '.');
                    }

                    // if the type is terbilang, read the referenced value and convert it
                    if ($tagData->input_type === 'terbilang' && !empty($value)) {
                        $angka = $request->dynamic_tags[$value] ?? 0;
                        $displayValue = \App\Helpers\Terbilang::make($angka);
                    }
                }

                // replace text inside the letter content
                $content = str_replace('[' . $tagName . ']', $displayValue, $content);
            }
        }

        $letter->update([
            'subject' => $request->subject,
            'content' => $content,
            'meta_data' => $request->has('dynamic_tags') ? json_encode($request->dynamic_tags) : null,
            'letter_type' => $request->letter_type,
            'letter_template_id' => $request->letter_template_id,
        ]);

        return redirect()->route('letters.show', $letter)->with('success', 'Letter updated successfully.');
    }

    public function destroy(Letter $letter)
    {
        $user = Auth::user();

        // determine admin status using roles::isadmin helper (exactly like index)
        $isAdmin = false;
        if ($user && $user->employee && $user->employee->role) {
            $isAdmin = \App\Constants\Roles::isAdmin($user->employee->role->title);
        }

        if ($isAdmin || ($user && $letter->user_id == $user->id && $letter->status === 'draft')) {
            // if conditions are met, delete the data
            $letter->delete();

            return redirect()->route('letters.index')->with('success', 'Letter permanently deleted successfully.');
        }

        // if it reaches here, it means they are trying to cheat (kick using 403)
        abort(403, 'Access Denied! You can only delete your own letters and the status MUST still be DRAFT.');
    }

    public function submit(Letter $letter)
    {
        // the correct logic: "if the status is not draft and not rejected, then reject"
        if (!in_array($letter->status, ['draft', 'rejected'])) {
            return redirect()->route('letters.index')->with('error', 'Letter cannot be submitted.');
        }

        $config = LetterConfiguration::first();
        if (!$config) {
            return redirect()->route('letters.index')->with('error', 'Letter configuration not set.');
        }


        $letter->update([
            'status' => 'pending',
            'reason' => null, // alright, use the 'reason' column
        ]);

        return redirect()->route('letters.show', $letter)->with('success', 'Letter submitted for approval.');
    }

    public function approve(Letter $letter)
    {
        // authorization check - only hr administrator and master admin can approve
        $user = Auth::user();
        if (
            !$user->employee ||
            ($user->employee->role->title !== 'HR Administrator' &&
                $user->employee->role->title !== \App\Constants\Roles::MASTER_ADMIN)
        ) {
            abort(403, 'Unauthorized action.');
        }

        if ($letter->status !== 'pending') {
            return redirect()->route('letters.show', $letter)->with('error', 'Letter cannot be approved.');
        }

        $config = LetterConfiguration::first();
        $config->current_number++;
        $config->save();
        
        $letterNumber = $this->generateLetterNumber($config, $letter);

        $letter->update([
            'status' => 'approved',
            'letter_number' => $letterNumber,
            'approver_id' => Auth::id(),
            'approved_date' => now(),
        ]);

        $template = LetterTemplate::find($letter->letter_template_id);

        if ($template && in_array($template->name, ['Surat Lupa Absen', 'Surat Telat Absen'])) {
            // decode the json we saved during creation
            $meta = json_decode($letter->meta_data, true);

            if ($meta) {
                $office = OfficeLocation::find($meta['lokasi_kantor']);

                Presence::updateOrCreate(
                    // parameter 1: search condition
                    [
                        'employee_id' => $letter->user->employee->id,
                        'date' => $meta['tanggal_lupa_absen'] ?? $meta['tanggal_telat_absen'],
                    ],
                    // parameter 2: data to be inserted or updated
                    [
                        'check_in' => ($meta['tanggal_lupa_absen'] ?? $meta['tanggal_telat_absen']) . ' 09:00:00',
                        'check_out' => ($meta['tanggal_lupa_absen'] ?? $meta['tanggal_telat_absen']) . ' 17:00:00',
                        'latitude' => $office->latitude ?? '0.000000',
                        'longitude' => $office->longitude ?? '0.000000',
                        'check_out_latitude' => $office->latitude ?? '0.000000',
                        'check_out_longitude' => $office->longitude ?? '0.000000',
                        'office_location_id' => $meta['lokasi_kantor'],
                        'work_type' => $meta['tipe_kehadiran_kerja'],
                        'status' => 'present',
                        'is_late' => 0,
                        'photo_path' => 'assets/images/default/admin-manual-presence.png',
                        'notes' =>
                            'Manually created/updated by letter system, letter number : ' . $letter->letter_number,
                    ],
                );
            }
        }

        return redirect()->route('letters.show', $letter)->with('success', 'Letter approved.');
    }

    public function reject(Request $request, Letter $letter)
    {
        // authorization check - only hr administrator and master admin can reject
        $user = Auth::user();
        if (
            !$user->employee ||
            ($user->employee->role->title !== 'HR Administrator' &&
                $user->employee->role->title !== \App\Constants\Roles::MASTER_ADMIN)
        ) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate(['reason' => 'required|string']);

        $letter->update([
            'status' => 'rejected',
            'reason' => $request->reason,
        ]);

        return redirect()->route('letters.show', $letter)->with('success', 'Letter rejected.');
    }

    // approve / reject pending letters (for reviewers)
    public function updateStatus(Request $request, Letter $letter)
    {
        $userRole = Auth::user()->employee->role->title ?? null;
        if (!in_array($userRole, ['Manager / Unit Head', 'HR Administrator', \App\Constants\Roles::MASTER_ADMIN])) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $letter->update([
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Letter status updated to ' . $request->status . '.');
    }

    public function print(Letter $letter)
    {
        // authorization check - only hr administrator and master admin can print
        $user = Auth::user();
        if (
            !$user->employee ||
            ($user->employee->role->title !== 'HR Administrator' &&
                $user->employee->role->title !== \App\Constants\Roles::MASTER_ADMIN)
        ) {
            abort(403, 'Unauthorized action.');
        }

        if ($letter->status !== 'approved') {
            return redirect()->route('letters.show', $letter)->with('error', 'Only approved letters can be printed.');
        }

        $letter->update([
            'status' => 'printed',
            'printed_date' => now(),
        ]);

        return redirect()->route('letters.show', $letter)->with('success', 'Letter marked as printed.');
    }

    public function export(Letter $letter)
    {
        if (!in_array($letter->status, ['approved', 'printed'])) {
            return redirect()
                ->route('letters.show', $letter)
                ->with('error', 'Only approved or printed letters can be exported.');
        }

        $config = LetterConfiguration::first();

        // first render (silently) to count physical pages
        $html = view('letters.pdf', compact('letter', 'config'))->render();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4', 'portrait');
        $pdf->render();
        $pageCount = $pdf->getCanvas()->get_page_count();

        // translate numbers to words (one, two, three...)
        $terbilang = [
            1 => 'One',
            2 => 'Two',
            3 => 'Three',
            4 => 'Four',
            5 => 'Five',
            6 => 'Six',
            7 => 'Seven',
            8 => 'Eight',
            9 => 'Nine',
            10 => 'Ten',
        ];
        $word = $terbilang[$pageCount] ?? $pageCount;
        $lampiranText = "{$word} ({$pageCount}) Sheets";

        // overwrite placeholder with final attachment text
        $finalHtml = str_replace('{TOTAL_PAGES_PLACEHOLDER}', $lampiranText, $html);

        // second render (final) and download
        $pdfFinal = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($finalHtml)->setPaper('a4', 'portrait');
        $filename = 'Letter_' . str_replace('/', '_', $letter->letter_number ?? 'Draft') . '.pdf';

        return $pdfFinal->download($filename);
    }

    private function generateLetterNumber(LetterConfiguration $config, Letter $letter)
    {
        $format = $config->letter_number_format;
        $number = str_pad($config->current_number, 3, '0', STR_PAD_LEFT);

        // convert month to roman numerals
        $romanMonths = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ];
        $month = $romanMonths[now()->month];

        $year = now()->year;

        $deptName = $letter->user->employee->department->name ?? 'GENERAL';
        $dept = strtoupper($deptName); // make it uppercase to keep the letter number neat

        return str_replace(['{NUMBER}', '{DEPT}', '{MONTH}', '{YEAR}'], [$number, $dept, $month, $year], $format);
    }

}
