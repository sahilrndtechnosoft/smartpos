<?php

namespace App\Http\Controllers;

use App\Models\KeyboardShortcut;
use App\Services\KeyboardShortcutResolver;
use App\Support\KeyboardShortcutActionTypes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RunKeyboardShortcutController extends Controller
{
    public function __invoke(
        Request $request,
        KeyboardShortcut $keyboardShortcut,
        KeyboardShortcutResolver $resolver,
    ): JsonResponse {
        abort_unless($request->user() !== null, 403);

        abort_unless(
            $keyboardShortcut->action_type === KeyboardShortcutActionTypes::RESOURCE_DELETE,
            404,
        );

        abort_unless(
            $resolver->userCanRunShortcut($keyboardShortcut, $request->user()),
            403,
        );

        $record = $resolver->resolveTargetRecord($keyboardShortcut);

        abort_if($record === null, 404, 'Target record not found.');

        $record->delete();

        return response()->json([
            'success' => true,
            'message' => 'Record deleted successfully.',
        ]);
    }
}
