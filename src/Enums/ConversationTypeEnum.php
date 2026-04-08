<?php

namespace Illimi\Communication\Enums;

/**
 * Conversation Type Enum
 */
enum ConversationTypeEnum: string
{
    case Direct = 'direct';
    case Group = 'group';
    case Announcement = 'announcement';
    case ParentTeacher = 'parent_teacher';

    public function label(): string
    {
        return match ($this) {
            self::Direct => 'Direct Message',
            self::Group => 'Group Chat',
            self::Announcement => 'Announcement',
            self::ParentTeacher => 'Parent-Teacher',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
