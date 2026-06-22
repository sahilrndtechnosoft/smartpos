<?php

namespace App\Support;

class KeyboardShortcutData
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function normalize(array $data): array
    {
        $actionType = $data['action_type'] ?? null;

        if (in_array($actionType, KeyboardShortcutActionTypes::clearsActionTarget(), true)) {
            $data['action_target'] = null;
            $data['action_record_id'] = null;
        }

        if (! in_array($actionType, KeyboardShortcutActionTypes::requiresRecordTarget(), true)) {
            $data['action_record_id'] = null;
        }

        return $data;
    }
}
