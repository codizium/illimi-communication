<?php

namespace Illimi\Communication\Enums;

enum ConversationParticipantRoleEnum: string
{
    case Member = 'member';
    case Admin = 'admin';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
