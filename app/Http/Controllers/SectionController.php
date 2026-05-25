<?php

/**
 * This file is part of the TodoList package.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Handles section (category) management.
 */
class SectionController extends Controller
{
    /**
     * Displays the section management form.
     *
     * @param Request $request The current HTTP request.
     *
     * @return View
     */
    public function sectionEdit(Request $request): View
    {
        $user = Auth::user();
        assert($user instanceof User);

        return view('section_edit', [
            'errors'   => session('controller_errors', []),
            'sections' => Section::where('user_id', $user->id)->orderBy('name')->get(),
        ]);
    }

    /**
     * Handles the section management form submission.
     *
     * @param Request $request The current HTTP request.
     *
     * @return RedirectResponse
     */
    public function sectionEditPost(Request $request): RedirectResponse
    {
        $user = Auth::user();
        assert($user instanceof User);

        $errors       = [];
        $submitButton = (string) $request->input('submitButton', '');

        if ($submitButton !== '') {
            try {
                DB::transaction(function () use ($request, $user, $submitButton, &$errors) {
                    if ($submitButton === 'Add') {
                        $name = trim((string) $request->input('add_name', ''));

                        if ($name !== '') {
                            (new Section())
                                ->setName($name)
                                ->setStatus('Active')
                                ->setUser($user)
                                ->save();
                        } else {
                            $errors[] = 'Please specify the name of the section to add.';
                        }
                    } elseif ($submitButton === 'Rename') {
                        $name = trim((string) $request->input('edit_name', ''));
                        $id   = (int) $request->input('edit_section_id', 0);

                        if ($id > 0) {
                            $section = Section::where('id', $id)
                                ->where('user_id', $user->id)
                                ->first();

                            if ($section !== null) {
                                $section->setName($name)->save();
                            }
                        } else {
                            $errors[] = 'Please specify a section to rename.';
                        }
                    } elseif ($submitButton === 'Activate') {
                        $query    = Section::where('user_id', $user->id)->where('status', 'Inactive');
                        $toggleId = $request->input('toggle_section_id');
                        if ($toggleId !== 'all') {
                            $query->where('id', (int) $toggleId);
                        }
                        $sections = $query->get();

                        foreach ($sections as $section) {
                            if ($request->input('resetStartTimes') !== null) {
                                Item::where('section_id', $section->id)
                                    ->where('status', 'Open')
                                    ->where('user_id', $user->id)
                                    ->get()
                                    ->each(fn (Item $item) => $item->save());
                            }
                            $section->setStatus('Active')->save();
                        }
                    } elseif ($submitButton === 'Deactivate') {
                        $query    = Section::where('user_id', $user->id)->where('status', 'Active');
                        $toggleId = $request->input('toggle_section_id');
                        if ($toggleId !== 'all') {
                            $query->where('id', (int) $toggleId);
                        }
                        $query->get()->each(fn (Section $section) => $section->setStatus('Inactive')->save());
                    }
                });
            } catch (\Exception $e) {
                $errors[] = sprintf('Failed to edit sections: %s', $e->getMessage());
            }
        }

        return redirect()->route('section_edit')->with('controller_errors', $errors);
    }
}
