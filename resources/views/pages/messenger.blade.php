@extends('layouts.app')

@push('styles')
    <style>
        #conversationSearchForm {
            width: 100%;
            max-width: 100%;
            min-width: 0;
        }

        #conversationSearchForm input[name="searchConversation"] {
            width: 100%;
            min-width: 0;
        }

        .chat-wrapper[data-communication-messenger] .conversation-mobile-back {
            display: none;
            flex-shrink: 0;
        }

        .chat-message-box {
            position: relative;
            overflow: visible !important;
        }

        .communication-draft-attachments {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            width: 100%;
            padding: 0 0 8px;
        }

        .communication-draft-attachments:empty {
            display: none;
        }

        .communication-draft-chip,
        .communication-file-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            border: 1px solid var(--bs-border-color, #dee2e6);
            border-radius: 999px;
            background: var(--bs-body-bg, #fff);
            font-size: 12px;
            line-height: 1.2;
        }

        .communication-draft-chip button {
            border: 0;
            background: transparent;
            color: var(--bs-secondary-color, #6c757d);
            padding: 0;
            line-height: 1;
        }

        .communication-message-attachments {
            display: grid;
            gap: 8px;
            margin-bottom: 12px;
        }

        .communication-message-images {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 160px));
            gap: 8px;
        }

        .communication-message-images a,
        .communication-message-images img {
            display: block;
            width: 100%;
            border-radius: 12px;
        }

        .communication-message-images img {
            height: 120px;
            object-fit: cover;
        }

        .communication-message-files {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 180px));
            gap: 8px;
        }

        .communication-file-chip {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 0;
            padding: 0;
            overflow: hidden;
            border-radius: 14px;
            background: var(--bs-body-bg, #fff);
            color: inherit;
            text-decoration: none;
        }

        .communication-file-thumb {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 104px;
            padding: 12px;
            background: var(--bs-tertiary-bg, #f8f9fa);
            font-size: 32px;
            color: var(--bs-primary, #0d6efd);
        }

        .communication-file-thumb video,
        .communication-file-thumb audio {
            width: 100%;
            border-radius: 10px;
        }

        .communication-file-meta {
            display: grid;
            gap: 4px;
            padding: 10px 12px 12px;
        }

        .communication-file-name {
            font-size: 12px;
            font-weight: 600;
            color: var(--bs-body-color, #212529);
            line-height: 1.3;
            word-break: break-word;
        }

        .communication-file-type {
            font-size: 11px;
            color: var(--bs-secondary-color, #6c757d);
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .communication-emoji-picker {
            position: absolute;
            right: 16px;
            bottom: calc(100% + 10px);
            width: min(320px, calc(100vw - 48px));
            padding: 12px;
            border: 1px solid var(--bs-border-color, #dee2e6);
            border-radius: 0.75rem;
            background: var(--bs-body-bg, #fff);
            box-shadow: var(--bs-box-shadow, 0 .5rem 1rem rgba(0, 0, 0, .15));
            z-index: 1080;
        }

        .communication-emoji-search {
            width: 100%;
            border: 1px solid var(--bs-border-color, #dee2e6);
            border-radius: 0.5rem;
            padding: 8px 10px;
            font-size: 13px;
            margin-bottom: 10px;
        }

        .communication-emoji-status {
            font-size: 12px;
            color: var(--bs-secondary-color, #6c757d);
            margin-bottom: 8px;
        }

        .communication-emoji-grid {
            display: grid;
            grid-template-columns: repeat(8, minmax(0, 1fr));
            gap: 6px;
            max-height: 240px;
            overflow-y: auto;
        }

        .communication-emoji-button {
            border: 0;
            background: transparent;
            border-radius: 10px;
            padding: 6px;
            display: grid;
            place-items: center;
        }

        .communication-emoji-button:hover {
            background: var(--bs-tertiary-bg, #f8f9fa);
        }

        .communication-emoji-button {
            font-size: 22px;
            line-height: 1;
        }

        .communication-message-emoji {
            font-size: 1.1em;
            line-height: 1;
            vertical-align: text-bottom;
        }

        #communicationMessageForm textarea[name="chatMessage"] {
            width: 100%;
            min-height: 44px;
            max-height: 140px;
            border: 0;
            background: transparent;
            box-shadow: none;
            resize: none;
            overflow-y: auto;
            padding: 0;
            line-height: 1.4;
            border-radius: 0;
            flex: 1 1 220px;
            min-width: 0;
        }

        #communicationMessageForm textarea[name="chatMessage"]:focus {
            outline: none;
            box-shadow: none;
        }

        #communicationMessageForm .chat-message-box-action {
            align-self: center;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        #communicationMessageForm .chat-message-box-action > button[type="button"] {
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0;
        }

        #communicationMessageForm .chat-message-box-action > button[type="submit"] {
            min-width: 88px;
        }

        @media (max-width: 991.98px) {
            .chat-wrapper[data-communication-messenger] {
                grid-template-columns: 1fr;
            }

            .chat-wrapper[data-communication-messenger].is-compact-list .chat-main {
                display: none;
            }

            .chat-wrapper[data-communication-messenger].is-compact-chat .chat-sidebar {
                display: none;
            }

            .chat-wrapper[data-communication-messenger].is-compact-chat .chat-main,
            .chat-wrapper[data-communication-messenger].is-compact-list .chat-sidebar {
                display: flex;
            }

            .chat-wrapper[data-communication-messenger] .chat-sidebar {
                max-height: min(78vh, 760px);
            }

            .chat-wrapper[data-communication-messenger] .chat-main {
                min-height: min(78vh, 760px);
            }

            .chat-wrapper[data-communication-messenger] .conversation-mobile-back {
                display: inline-flex;
            }

            .chat-wrapper[data-communication-messenger] .chat-single-message .chat-message-content {
                max-width: 84%;
            }
        }

        @media (max-width: 767.98px) {
            .chat-wrapper[data-communication-messenger] .chat-sidebar {
                max-height: 42vh;
            }

            .chat-wrapper[data-communication-messenger] .chat-sidebar-single .img img,
            .chat-wrapper[data-communication-messenger] #conversationHeader .img img {
                width: 42px;
                height: 42px;
            }

            .chat-wrapper[data-communication-messenger] #conversationHeader {
                display: grid;
                grid-template-columns: auto auto minmax(0, 1fr);
                align-items: center;
                gap: 10px;
                padding: 12px 14px;
            }

            .chat-wrapper[data-communication-messenger] #conversationHeader .info {
                min-width: 0;
            }

            .chat-wrapper[data-communication-messenger] #conversationHeader .info h6,
            .chat-wrapper[data-communication-messenger] #conversationHeader .info p {
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .chat-wrapper[data-communication-messenger] #conversationHeader .action {
                grid-column: 1 / -1;
                justify-content: space-between;
                width: 100%;
                gap: 10px !important;
            }

            .chat-message-box {
                padding: 10px 12px;
                gap: 10px;
            }

            #communicationMessageForm textarea[name="chatMessage"] {
                flex-basis: 100%;
                order: 1;
            }

            #communicationMessageForm .chat-message-box-action {
                order: 2;
                width: 100%;
                justify-content: space-between;
            }

            #communicationMessageForm .chat-message-box-action > button[type="submit"] {
                min-width: 72px;
            }

            .communication-emoji-picker {
                right: 10px;
                left: 10px;
                width: auto;
                bottom: calc(100% + 8px);
            }

            .communication-message-images,
            .communication-message-files {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 575.98px) {
            .chat-wrapper[data-communication-messenger] .chat-sidebar {
                max-height: calc(100vh - 220px);
            }

            .communication-emoji-grid {
                grid-template-columns: repeat(6, minmax(0, 1fr));
            }

            .communication-message-images,
            .communication-message-files {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    <div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">Message</h1>
            <div>
                <a href="{{ route('home') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <span class="text-secondary-light">/ Message</span>
            </div>
        </div>
    </div>

    <div class="chat-wrapper" data-communication-messenger data-api-base="{{ $apiBase }}"
        data-current-user-id="{{ auth()->id() }}">
        <div class="chat-sidebar card">
            <div class="px-24 pt-24 pb-16 border-bottom">
                <div class="d-flex align-items-center justify-content-between gap-3 mb-16">
                    <div>
                        <h6 class="mb-4">Conversations</h6>
                    </div>
                    <button type="button"
                        class="btn btn-sm btn-primary-600 radius-8 d-inline-flex align-items-center gap-1"
                        data-bs-toggle="modal" data-bs-target="#newConversationModal">
                        <i class="ri-add-line"></i>
                    </button>
                </div>
                <form class="navbar-search w-100" id="conversationSearchForm">
                    <input type="text" class="bg-transparent" name="searchConversation"
                        placeholder="Search conversations">
                    <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                </form>
            </div>
            <div class="chat-all-list" id="conversationList">
                <div class="p-24 text-center text-secondary-light">Loading conversations...</div>
            </div>
        </div>

        <div class="chat-main card">
            <div class="chat-sidebar-single active" id="conversationHeader">
                <button type="button" class="conversation-mobile-back" id="conversationMobileBackBtn"
                    aria-label="Back to conversations">
                    <iconify-icon icon="solar:alt-arrow-left-linear"></iconify-icon>
                </button>
                <div class="img">
                    <img src="{{ asset('assets/images/logo-icon.png') }}" alt="Conversation" id="conversationAvatar">
                </div>
                <div class="info">
                    <h6 class="text-md mb-0" id="conversationTitle">Select a conversation</h6>
                    <p class="mb-0" id="conversationMeta">Messages will appear here</p>
                </div>
                <div class="action d-inline-flex align-items-center gap-3">
                    <span class="badge bg-success-focus text-success-main">Live updates enabled</span>
                    <div class="btn-group">
                        <button type="button" class="text-primary-light text-xl" data-bs-toggle="dropdown"
                            data-bs-display="static" aria-expanded="false">
                            <iconify-icon icon="tabler:dots-vertical"></iconify-icon>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-lg-end border">
                            <li>
                                <button
                                    class="dropdown-item rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-2"
                                    type="button" id="archiveConversationBtn">
                                    <iconify-icon icon="ic:outline-archive"></iconify-icon>
                                    Archive
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="chat-message-list" id="communicationMessageList">
                <div class="p-24 text-center text-secondary-light">Select a conversation to load messages.</div>
            </div>

            <form class="chat-message-box" id="communicationMessageForm">
                <div class="communication-draft-attachments" id="communicationDraftAttachments"></div>
                <textarea name="chatMessage" placeholder="Write message" rows="1" disabled></textarea>
                <input type="file" id="communicationAttachmentInput" class="d-none" multiple>
                <input type="file" id="communicationImageInput" class="d-none" accept="image/*" multiple>
                <div class="chat-message-box-action">
                    <button type="button" class="text-xl" aria-label="Insert emoji" id="toggleEmojiPickerButton">
                        <iconify-icon icon="solar:smile-circle-linear"></iconify-icon>
                    </button>
                    <button type="button" class="text-xl" aria-label="Attach file" id="attachFileButton">
                        <iconify-icon icon="ph:link"></iconify-icon>
                    </button>
                    <button type="button" class="text-xl" aria-label="Attach media" id="attachImageButton">
                        <iconify-icon icon="solar:gallery-linear"></iconify-icon>
                    </button>
                    <button type="submit"
                        class="btn btn-sm btn-primary-600 radius-8 d-inline-flex align-items-center gap-1" disabled>
                        Send
                        <iconify-icon icon="f7:paperplane"></iconify-icon>
                    </button>
                </div>
                <div class="communication-emoji-picker d-none" id="communicationEmojiPicker">
                    <input type="text" class="communication-emoji-search" id="communicationEmojiSearch"
                        placeholder="Search emojis">
                    <div class="communication-emoji-status" id="communicationEmojiStatus">Showing emojis</div>
                    <div class="communication-emoji-grid" id="communicationEmojiGrid"></div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="newConversationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form id="newConversationForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Start New Conversation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Conversation Type</label>
                                <select class="form-select" name="type">
                                    <option value="direct">Direct</option>
                                    <option value="group">Group</option>
                                    <option value="announcement">Announcement</option>
                                    <option value="parent_teacher">Parent-Teacher</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Title</label>
                                <input type="text" class="form-control" name="title"
                                    placeholder="Optional for direct chats">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Participants</label>
                                <select class="form-select choice" name="participant_ids[]" id="conversationParticipants"
                                    multiple size="10">
                                    @foreach ($availableUsers as $user)
                                        <option value="{{ $user['id'] }}">
                                            {{ $user['name'] }}{{ $user['email'] ? ' - ' . $user['email'] : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-secondary-light">Only non-student users are listed here.</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary-600">Create Conversation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const root = document.querySelector('[data-communication-messenger]');
            const form = document.getElementById('communicationMessageForm');
            const list = document.getElementById('communicationMessageList');
            const conversationList = document.getElementById('conversationList');
            const conversationAvatar = document.getElementById('conversationAvatar');
            const conversationTitle = document.getElementById('conversationTitle');
            const conversationMeta = document.getElementById('conversationMeta');
            const conversationMobileBackButton = document.getElementById('conversationMobileBackBtn');
            const draftAttachments = document.getElementById('communicationDraftAttachments');
            const sendInput = form?.querySelector('textarea[name="chatMessage"]');
            const sendButton = form?.querySelector('button[type="submit"]');
            const emojiPicker = document.getElementById('communicationEmojiPicker');
            const emojiGrid = document.getElementById('communicationEmojiGrid');
            const emojiSearchInput = document.getElementById('communicationEmojiSearch');
            const emojiStatus = document.getElementById('communicationEmojiStatus');
            const toggleEmojiPickerButton = document.getElementById('toggleEmojiPickerButton');
            const attachmentInput = document.getElementById('communicationAttachmentInput');
            const imageInput = document.getElementById('communicationImageInput');
            const attachFileButton = document.getElementById('attachFileButton');
            const attachImageButton = document.getElementById('attachImageButton');
            const archiveButton = document.getElementById('archiveConversationBtn');
            const searchInput = document.querySelector('input[name="searchConversation"]');
            const newConversationForm = document.getElementById('newConversationForm');
            const apiBase = root?.dataset.apiBase || '{{ $apiBase }}';
            const currentUserId = root?.dataset.currentUserId || '';
            const defaultAvatar = `{{ asset('assets/images/logo-icon.png') }}`;
            const notificationSoundSrc = `{{ asset('sounds/notification-01.mp3') }}`;
            const bootstrapModal = window.bootstrap?.Modal;
            const onlineUsers = new Map();
            const notificationAudio = new Audio(notificationSoundSrc);
            const messengerEmojis = [{
                    emoji: '😀',
                    name: 'grinning face',
                    keywords: ['grin', 'happy', 'smile']
                },
                {
                    emoji: '😄',
                    name: 'smiling face with open mouth',
                    keywords: ['smile', 'happy', 'joy']
                },
                {
                    emoji: '😁',
                    name: 'beaming face',
                    keywords: ['grin', 'teeth', 'happy']
                },
                {
                    emoji: '😂',
                    name: 'face with tears of joy',
                    keywords: ['laugh', 'funny', 'cry']
                },
                {
                    emoji: '🤣',
                    name: 'rolling on the floor laughing',
                    keywords: ['rofl', 'laugh', 'funny']
                },
                {
                    emoji: '😊',
                    name: 'smiling face with smiling eyes',
                    keywords: ['blush', 'warm', 'kind']
                },
                {
                    emoji: '🙂',
                    name: 'slightly smiling face',
                    keywords: ['smile', 'calm']
                },
                {
                    emoji: '😉',
                    name: 'winking face',
                    keywords: ['wink', 'playful']
                },
                {
                    emoji: '😍',
                    name: 'smiling face with heart eyes',
                    keywords: ['love', 'heart', 'crush']
                },
                {
                    emoji: '😘',
                    name: 'face blowing a kiss',
                    keywords: ['kiss', 'love']
                },
                {
                    emoji: '😎',
                    name: 'smiling face with sunglasses',
                    keywords: ['cool', 'chill']
                },
                {
                    emoji: '🤔',
                    name: 'thinking face',
                    keywords: ['think', 'hmm']
                },
                {
                    emoji: '😴',
                    name: 'sleeping face',
                    keywords: ['sleep', 'tired']
                },
                {
                    emoji: '😭',
                    name: 'loudly crying face',
                    keywords: ['cry', 'sad']
                },
                {
                    emoji: '😢',
                    name: 'crying face',
                    keywords: ['sad', 'tear']
                },
                {
                    emoji: '😡',
                    name: 'pouting face',
                    keywords: ['angry', 'mad']
                },
                {
                    emoji: '😱',
                    name: 'face screaming in fear',
                    keywords: ['shock', 'surprised']
                },
                {
                    emoji: '👍',
                    name: 'thumbs up',
                    keywords: ['like', 'approve', 'yes']
                },
                {
                    emoji: '👎',
                    name: 'thumbs down',
                    keywords: ['dislike', 'no']
                },
                {
                    emoji: '👏',
                    name: 'clapping hands',
                    keywords: ['clap', 'applause']
                },
                {
                    emoji: '🙌',
                    name: 'raising hands',
                    keywords: ['celebrate', 'yay']
                },
                {
                    emoji: '🙏',
                    name: 'folded hands',
                    keywords: ['pray', 'thanks', 'please']
                },
                {
                    emoji: '👋',
                    name: 'waving hand',
                    keywords: ['hello', 'hi', 'bye']
                },
                {
                    emoji: '💪',
                    name: 'flexed biceps',
                    keywords: ['strong', 'muscle']
                },
                {
                    emoji: '✌️',
                    name: 'victory hand',
                    keywords: ['peace', 'victory']
                },
                {
                    emoji: '🤝',
                    name: 'handshake',
                    keywords: ['deal', 'agree']
                },
                {
                    emoji: '❤️',
                    name: 'red heart',
                    keywords: ['love', 'heart']
                },
                {
                    emoji: '💙',
                    name: 'blue heart',
                    keywords: ['heart', 'blue']
                },
                {
                    emoji: '💚',
                    name: 'green heart',
                    keywords: ['heart', 'green']
                },
                {
                    emoji: '💔',
                    name: 'broken heart',
                    keywords: ['sad', 'heartbreak']
                },
                {
                    emoji: '🔥',
                    name: 'fire',
                    keywords: ['lit', 'hot']
                },
                {
                    emoji: '✨',
                    name: 'sparkles',
                    keywords: ['sparkle', 'magic']
                },
                {
                    emoji: '🎉',
                    name: 'party popper',
                    keywords: ['party', 'celebrate']
                },
                {
                    emoji: '🎊',
                    name: 'confetti ball',
                    keywords: ['celebration', 'party']
                },
                {
                    emoji: '🎁',
                    name: 'wrapped gift',
                    keywords: ['gift', 'present']
                },
                {
                    emoji: '🏆',
                    name: 'trophy',
                    keywords: ['win', 'award']
                },
                {
                    emoji: '💯',
                    name: 'hundred points',
                    keywords: ['100', 'perfect']
                },
                {
                    emoji: '✅',
                    name: 'check mark button',
                    keywords: ['check', 'done', 'yes']
                },
                {
                    emoji: '❌',
                    name: 'cross mark',
                    keywords: ['cancel', 'no']
                },
                {
                    emoji: '⚠️',
                    name: 'warning',
                    keywords: ['alert', 'warning']
                },
                {
                    emoji: '❓',
                    name: 'question mark',
                    keywords: ['question', 'ask']
                },
                {
                    emoji: '💡',
                    name: 'light bulb',
                    keywords: ['idea', 'smart']
                },
                {
                    emoji: '📌',
                    name: 'pushpin',
                    keywords: ['pin', 'important']
                },
                {
                    emoji: '📎',
                    name: 'paperclip',
                    keywords: ['attachment', 'file']
                },
                {
                    emoji: '📷',
                    name: 'camera',
                    keywords: ['photo', 'image']
                },
                {
                    emoji: '🎵',
                    name: 'musical note',
                    keywords: ['music', 'song']
                },
                {
                    emoji: '🎤',
                    name: 'microphone',
                    keywords: ['mic', 'voice']
                },
                {
                    emoji: '💻',
                    name: 'laptop',
                    keywords: ['computer', 'work']
                },
                {
                    emoji: '📱',
                    name: 'mobile phone',
                    keywords: ['phone', 'mobile']
                },
                {
                    emoji: '🚀',
                    name: 'rocket',
                    keywords: ['launch', 'fast']
                },
                {
                    emoji: '☕',
                    name: 'hot beverage',
                    keywords: ['coffee', 'tea']
                },
                {
                    emoji: '🍕',
                    name: 'pizza',
                    keywords: ['food', 'eat']
                },
                {
                    emoji: '🍔',
                    name: 'hamburger',
                    keywords: ['food', 'burger']
                },
                {
                    emoji: '🎂',
                    name: 'birthday cake',
                    keywords: ['birthday', 'cake']
                },
                {
                    emoji: '⚽',
                    name: 'soccer ball',
                    keywords: ['football', 'sport']
                },
                {
                    emoji: '🏀',
                    name: 'basketball',
                    keywords: ['sport', 'ball']
                },
                {
                    emoji: '🐶',
                    name: 'dog face',
                    keywords: ['dog', 'pet']
                },
                {
                    emoji: '🐱',
                    name: 'cat face',
                    keywords: ['cat', 'pet']
                },
                {
                    emoji: '🌞',
                    name: 'sun with face',
                    keywords: ['sun', 'bright']
                },
                {
                    emoji: '🌙',
                    name: 'crescent moon',
                    keywords: ['night', 'moon']
                }
            ];

            let conversations = [];
            let activeConversationId = null;
            let currentMessages = [];
            let pendingAttachments = [];
            let presenceHeartbeatId = null;
            let audioUnlocked = false;
            let compactMessengerView = 'list';

            if (!form || !list || !conversationList || !conversationTitle || !conversationMeta || !sendInput || !
                sendButton) {
                return;
            }

            function formatTime(value) {
                if (!value) return '';
                const date = new Date(value);
                if (Number.isNaN(date.getTime())) return '';
                return date.toLocaleTimeString([], {
                    hour: 'numeric',
                    minute: '2-digit'
                });
            }

            function formatSidebarTime(value) {
                if (!value) return '';
                const date = new Date(value);
                if (Number.isNaN(date.getTime())) return '';
                return date.toLocaleDateString([], {
                    month: 'short',
                    day: 'numeric'
                });
            }

            function initialsFromName(value) {
                const parts = String(value || '').trim().split(/\s+/).filter(Boolean);
                if (!parts.length) {
                    return 'U';
                }

                return parts.slice(0, 2).map((part) => part.charAt(0).toUpperCase()).join('');
            }

            function placeholderAvatar(name) {
                const initials = initialsFromName(name);
                const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="96" height="96" viewBox="0 0 96 96">
                    <rect width="96" height="96" rx="48" fill="#2D4F8C"/>
                    <text x="50%" y="50%" dominant-baseline="central" text-anchor="middle" font-family="Arial, sans-serif" font-size="34" font-weight="700" fill="#ffffff">${initials}</text>
                </svg>`;

                return `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(svg)}`;
            }

            function avatarUrlForUser(user, fallbackName = 'User') {
                const imageUrl = String(user?.avatar_url || '').trim();
                return imageUrl || placeholderAvatar(user?.name || fallbackName);
            }

            function isCompactMessengerView() {
                return window.matchMedia('(max-width: 991.98px)').matches;
            }

            function syncCompactMessengerLayout(nextView = null) {
                if (!root) {
                    return;
                }

                if (nextView === 'chat' || nextView === 'list') {
                    compactMessengerView = nextView;
                }

                root.classList.remove('is-compact-list', 'is-compact-chat');

                if (!isCompactMessengerView()) {
                    return;
                }

                root.classList.add(compactMessengerView === 'chat' ? 'is-compact-chat' : 'is-compact-list');
            }

            function showConversationPlaceholder(title = 'Select a conversation', meta =
                'Messages will appear here') {
                activeConversationId = null;
                conversationTitle.textContent = title;
                conversationMeta.textContent = meta;
                if (conversationAvatar) {
                    conversationAvatar.src = defaultAvatar;
                    conversationAvatar.alt = title;
                }
                list.innerHTML =
                    '<div class="p-24 text-center text-secondary-light">Select a conversation to load messages.</div>';
                setComposerState(false);
            }

            function primeNotificationAudio() {
                const unlock = () => {
                    if (audioUnlocked) {
                        return;
                    }

                    audioUnlocked = true;
                    notificationAudio.muted = true;
                    notificationAudio.currentTime = 0;
                    notificationAudio.play().then(() => {
                        notificationAudio.pause();
                        notificationAudio.currentTime = 0;
                        notificationAudio.muted = false;
                    }).catch(() => {
                        audioUnlocked = false;
                    });

                    document.removeEventListener('click', unlock, true);
                    document.removeEventListener('keydown', unlock, true);
                };

                notificationAudio.preload = 'auto';
                notificationAudio.load();
                document.addEventListener('click', unlock, true);
                document.addEventListener('keydown', unlock, true);
            }

            function playNotificationSound(kind) {
                const clip = kind === 'outgoing' ? {
                    start: 0.5,
                    end: 2
                } : {
                    start: 0,
                    end: 0.5
                };

                const stopPlayback = () => {
                    notificationAudio.pause();
                    notificationAudio.currentTime = 0;
                };

                const startPlayback = () => {
                    notificationAudio.currentTime = clip.start;
                    notificationAudio.play().catch(() => {});
                    window.setTimeout(stopPlayback, Math.max(0, (clip.end - clip.start) * 1000));
                };

                if (notificationAudio.readyState >= 1) {
                    startPlayback();
                } else {
                    notificationAudio.addEventListener('loadedmetadata', startPlayback, {
                        once: true
                    });
                    notificationAudio.addEventListener('error', stopPlayback, {
                        once: true
                    });
                    notificationAudio.load();
                }
            }

            function escapeHtml(value) {
                return String(value ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            function renderEmojiPicker() {
                if (!emojiGrid) {
                    return;
                }

                const query = String(emojiSearchInput?.value || '').trim().toLowerCase();
                const items = !query ? messengerEmojis : messengerEmojis.filter((item) => {
                    const haystack = [item.name, ...(Array.isArray(item.keywords) ? item.keywords : [])].join(' ')
                        .toLowerCase();
                    return haystack.includes(query);
                });

                emojiGrid.innerHTML = items.map((item) => `
                    <button type="button" class="communication-emoji-button" data-emoji="${escapeHtml(item.emoji)}" title="${escapeHtml(item.name)}" aria-label="Insert ${escapeHtml(item.name)}">
                        <span class="communication-message-emoji">${escapeHtml(item.emoji)}</span>
                    </button>
                `).join('');

                if (emojiStatus) {
                    if (!items.length) {
                        emojiStatus.textContent = 'No emojis found.';
                    } else {
                        emojiStatus.textContent = query ?
                            `${items.length} result(s)` :
                            `Showing ${items.length} emojis`;
                    }
                }
            }

            function formatMessageBody(value) {
                return escapeHtml(value ?? '').replace(/\n/g, '<br>');
            }

            function conversationName(conversation) {
                const participants = Array.isArray(conversation?.participants) ? conversation.participants : [];
                const others = participants.filter((participant) => String(participant.user_id || '') !== String(
                    currentUserId));
                if (conversation?.title) return conversation.title;
                if (!others.length) return 'You';
                return others.map((participant) => participant.user?.name || participant.user?.email || 'Participant')
                    .join(', ');
            }

            function conversationPreview(conversation) {
                const body = String(conversation?.latest_message?.body || '').trim();
                if (body) {
                    return body;
                }

                const attachments = Array.isArray(conversation?.latest_message?.attachments) ? conversation.latest_message
                    .attachments : [];
                if (attachments.length) {
                    return attachments.length === 1 ? 'Attachment' : `${attachments.length} attachments`;
                }

                return 'No messages yet';
            }

            function conversationById(conversationId) {
                return conversations.find((item) => String(item.id) === String(conversationId));
            }

            function conversationAvatarUrl(conversation) {
                const others = otherParticipants(conversation);
                const primaryParticipant = others[0] || (Array.isArray(conversation?.participants) ? conversation
                    .participants[0] : null);
                const user = primaryParticipant?.user || null;

                return avatarUrlForUser(user, conversationName(conversation));
            }

            function otherParticipants(conversation) {
                const participants = Array.isArray(conversation?.participants) ? conversation.participants : [];
                return participants.filter((participant) => String(participant.user_id || '') !== String(
                    currentUserId));
            }

            function participantIsOnline(participant) {
                const userId = String(participant?.user_id || participant?.user?.id || '');
                if (!userId) {
                    return false;
                }

                if (onlineUsers.has(userId)) {
                    return Boolean(onlineUsers.get(userId));
                }

                return Boolean(participant?.is_online);
            }

            function onlineCount(conversation) {
                return otherParticipants(conversation).filter((participant) => participantIsOnline(participant)).length;
            }

            function conversationPresenceLabel(conversation) {
                const others = otherParticipants(conversation);
                if (!others.length) {
                    return '';
                }

                if ((conversation?.type || '') === 'direct' && others.length === 1) {
                    return participantIsOnline(others[0]) ? 'Online' : 'Offline';
                }

                const count = onlineCount(conversation);
                return count > 0 ? `${count} online` : 'No one online';
            }

            function syncPresenceMap(items) {
                (Array.isArray(items) ? items : []).forEach((conversation) => {
                    (Array.isArray(conversation?.participants) ? conversation.participants : []).forEach((
                        participant) => {
                        const userId = String(participant?.user_id || participant?.user?.id || '');
                        if (!userId) {
                            return;
                        }

                        onlineUsers.set(userId, Boolean(participant?.is_online));
                    });
                });
            }

            function setUserOnlineStatus(userId, isOnline) {
                const normalizedUserId = String(userId || '');
                if (!normalizedUserId) {
                    return;
                }

                onlineUsers.set(normalizedUserId, Boolean(isOnline));

                conversations = conversations.map((conversation) => ({
                    ...conversation,
                    participants: (Array.isArray(conversation?.participants) ? conversation.participants :
                    []).map((participant) => {
                        const participantUserId = String(participant?.user_id || participant?.user
                            ?.id || '');
                        if (participantUserId !== normalizedUserId) {
                            return participant;
                        }

                        return {
                            ...participant,
                            is_online: Boolean(isOnline),
                        };
                    }),
                }));
            }

            function refreshActiveConversationMeta() {
                if (!activeConversationId) {
                    return;
                }

                const conversation = conversations.find((item) => String(item.id) === String(activeConversationId));
                if (!conversation) {
                    return;
                }

                const participantsTotal = conversation.participants_count || conversation.participants?.length || 0;
                const presenceLabel = conversationPresenceLabel(conversation);
                conversationMeta.textContent = presenceLabel ?
                    `${participantsTotal} participant(s) • ${presenceLabel}` :
                    `${participantsTotal} participant(s)`;
            }

            async function heartbeatPresence() {
                return requestJson(`${apiBase}/presence/heartbeat`, {
                    method: 'post'
                });
            }

            function startPresenceHeartbeat() {
                if (presenceHeartbeatId) {
                    window.clearInterval(presenceHeartbeatId);
                }

                heartbeatPresence().catch(() => {});
                presenceHeartbeatId = window.setInterval(() => {
                    heartbeatPresence().catch(() => {});
                }, 60000);
            }

            function setComposerState(enabled) {
                sendInput.disabled = !enabled;
                sendButton.disabled = !enabled;
                if (attachFileButton) {
                    attachFileButton.disabled = !enabled;
                }
                if (attachImageButton) {
                    attachImageButton.disabled = !enabled;
                }
                if (archiveButton) {
                    archiveButton.disabled = !enabled;
                }
                if (!enabled) {
                    sendInput.value = '';
                    autoResizeComposer();
                }
            }

            function autoResizeComposer() {
                if (!sendInput) {
                    return;
                }

                sendInput.style.height = 'auto';
                sendInput.style.height = `${Math.min(sendInput.scrollHeight, 140)}px`;
            }

            function renderConversations(items) {
                if (!items.length) {
                    conversationList.innerHTML =
                        '<div class="p-24 text-center text-secondary-light">No conversations found.</div>';
                    return;
                }

                conversationList.innerHTML = items.map((conversation) => {
                    const name = conversationName(conversation);
                    const avatarUrl = conversationAvatarUrl(conversation);
                    const preview = conversationPreview(conversation);
                    const when = formatSidebarTime(conversation.last_message_at || conversation.updated_at);
                    const isActive = String(conversation.id) === String(activeConversationId);
                    const presenceLabel = conversationPresenceLabel(conversation);
                    const presenceClass = presenceLabel === 'Online' || /\bonline\b/i.test(presenceLabel) ?
                        'text-success-main' :
                        'text-secondary-light';
                    return `
                    <button type="button" class="chat-sidebar-single ${isActive ? 'active' : ''} border-0 bg-transparent text-start w-100" data-conversation-id="${escapeHtml(conversation.id)}">
                        <div class="img">
                            <img src="${avatarUrl}" alt="${escapeHtml(name)}">
                        </div>
                        <div class="info">
                            <h6 class="text-sm mb-1">${escapeHtml(name)}</h6>
                            <p class="mb-0 text-xs">${escapeHtml(preview)}</p>
                            ${presenceLabel ? `<span class="text-xs ${presenceClass}">${escapeHtml(presenceLabel)}</span>` : ''}
                        </div>
                        <div class="action text-end">
                            <p class="mb-0 text-neutral-400 text-xs lh-1">${escapeHtml(when)}</p>
                            <span class="text-secondary-light text-xs">${escapeHtml(conversation.type_label || '')}</span>
                        </div>
                    </button>`;
                }).join('');
            }

            function renderMessages(messages) {
                currentMessages = Array.isArray(messages) ? messages.slice() : [];

                if (!messages.length) {
                    list.innerHTML =
                        '<div class="p-24 text-center text-secondary-light">No messages yet. Start the conversation.</div>';
                    return;
                }

                list.innerHTML = messages.map((message) => {
                    const isOwnMessage = String(message.sender_id || '') === String(currentUserId);
                    const side = isOwnMessage ? 'right' : 'left';
                    const avatar = isOwnMessage ? '' :
                        `<img src="${avatarUrlForUser(message.sender, message.sender?.name || 'User')}" alt="${escapeHtml(message.sender?.name || 'User')}" class="object-fit-cover rounded-circle" style="width:42px;height:42px;">`;
                    const statusHtml = isOwnMessage ? messageStatusMarkup(message) : '';
                    const attachmentsHtml = renderMessageAttachments(message);
                    return `
                    <div class="chat-single-message ${side}">
                        ${avatar}
                        <div class="chat-message-content">
                            ${attachmentsHtml}
                            <p class="mb-3">${formatMessageBody(message.body || '')}</p>
                            <p class="chat-time mb-0 d-inline-flex align-items-center gap-2">
                                <span>${escapeHtml(formatTime(message.created_at))}</span>
                                ${statusHtml}
                            </p>
                        </div>
                    </div>
                `;
                }).join('');
                list.scrollTop = list.scrollHeight;
            }

            function renderMessageAttachments(message) {
                const attachments = Array.isArray(message?.attachments) ? message.attachments : [];
                if (!attachments.length) {
                    return '';
                }

                const images = attachments.filter((attachment) => attachment?.is_image);
                const files = attachments.filter((attachment) => !attachment?.is_image);

                const imagesHtml = images.length ? `
                    <div class="communication-message-images">
                        ${images.map((attachment) => `
                            <a href="${escapeHtml(attachment.file_url || '#')}" target="_blank" rel="noopener noreferrer">
                                <img src="${escapeHtml(attachment.file_url || '')}" alt="${escapeHtml(attachment.label || attachment.file_name || 'Attachment')}">
                            </a>
                        `).join('')}
                    </div>
                ` : '';

                const filesHtml = files.length ? `
                    <div class="communication-message-files">
                        ${files.map((attachment) => renderFileAttachmentCard(attachment)).join('')}
                    </div>
                ` : '';

                return `<div class="communication-message-attachments">${imagesHtml}${filesHtml}</div>`;
            }

            function fileExtension(attachment) {
                const name = String(attachment?.file_name || attachment?.label || '').trim();
                const parts = name.split('.');
                return parts.length > 1 ? parts.pop().toUpperCase() : 'FILE';
            }

            function fileTypeLabel(attachment) {
                const mime = String(attachment?.file_type || '').trim();
                if (!mime) {
                    return fileExtension(attachment);
                }

                const [group, subtype] = mime.split('/');
                if (group === 'video') return 'VIDEO';
                if (group === 'audio') return 'AUDIO';
                if (mime === 'application/pdf') return 'PDF';
                if (subtype) return subtype.replace(/[-+.]/g, ' ').toUpperCase();
                return fileExtension(attachment);
            }

            function renderFileAttachmentCard(attachment) {
                const fileUrl = escapeHtml(attachment?.file_url || '#');
                const fileName = escapeHtml(attachment?.label || attachment?.file_name || 'Attachment');
                const fileType = String(attachment?.file_type || '').trim().toLowerCase();
                const extension = escapeHtml(fileExtension(attachment));
                const typeLabel = escapeHtml(fileTypeLabel(attachment));

                let thumbnail = `<div class="communication-file-thumb"><div class="text-center">
                    <i class="ri-file-3-line d-block mb-1"></i>
                    <div class="fw-semibold text-sm">${extension}</div>
                </div></div>`;

                if (fileType.startsWith('video/')) {
                    thumbnail = `<div class="communication-file-thumb">
                        <video src="${fileUrl}" preload="metadata" controls muted playsinline></video>
                    </div>`;
                } else if (fileType.startsWith('audio/')) {
                    thumbnail = `<div class="communication-file-thumb">
                        <audio src="${fileUrl}" preload="metadata" controls></audio>
                    </div>`;
                } else if (fileType === 'application/pdf') {
                    thumbnail = `<div class="communication-file-thumb"><div class="text-center">
                        <i class="ri-file-pdf-2-line d-block mb-1"></i>
                        <div class="fw-semibold text-sm">PDF</div>
                    </div></div>`;
                } else if (fileType.includes('word')) {
                    thumbnail = `<div class="communication-file-thumb"><div class="text-center">
                        <i class="ri-file-word-2-line d-block mb-1"></i>
                        <div class="fw-semibold text-sm">${extension}</div>
                    </div></div>`;
                } else if (fileType.includes('sheet') || fileType.includes('excel') || fileType.includes('csv')) {
                    thumbnail = `<div class="communication-file-thumb"><div class="text-center">
                        <i class="ri-file-excel-2-line d-block mb-1"></i>
                        <div class="fw-semibold text-sm">${extension}</div>
                    </div></div>`;
                } else if (fileType.includes('presentation') || fileType.includes('powerpoint')) {
                    thumbnail = `<div class="communication-file-thumb"><div class="text-center">
                        <i class="ri-file-ppt-2-line d-block mb-1"></i>
                        <div class="fw-semibold text-sm">${extension}</div>
                    </div></div>`;
                } else if (fileType.includes('zip') || fileType.includes('rar') || fileType.includes('archive')) {
                    thumbnail = `<div class="communication-file-thumb"><div class="text-center">
                        <i class="ri-file-zip-line d-block mb-1"></i>
                        <div class="fw-semibold text-sm">${extension}</div>
                    </div></div>`;
                }

                return `<a href="${fileUrl}" target="_blank" rel="noopener noreferrer" class="communication-file-chip">
                    ${thumbnail}
                    <div class="communication-file-meta">
                        <span class="communication-file-name">${fileName}</span>
                        <span class="communication-file-type">${typeLabel}</span>
                    </div>
                </a>`;
            }

            function messageStatusMarkup(message) {
                const status = String(message?.status || 'sent').toLowerCase();
                const config = {
                    sent: {
                        icon: 'ri-check-line',
                        label: 'Sent',
                        cls: 'text-white-75',
                    },
                    delivered: {
                        icon: 'ri-check-double-line',
                        label: 'Delivered',
                        cls: 'text-warning-200',
                    },
                    read: {
                        icon: 'ri-check-double-line',
                        label: 'Read',
                        cls: 'text-info-200',
                    },
                }[status] || {
                    icon: 'ri-check-line',
                    label: 'Sent',
                    cls: 'text-white-75',
                };

                return `<span class="d-inline-flex align-items-center ${config.cls}" title="${escapeHtml(config.label)}" aria-label="${escapeHtml(config.label)}">
                    <i class="${config.icon} text-sm fw-semibold"></i>
                </span>`;
            }

            function requestJson(url, options = {}) {
                const isFormData = typeof FormData !== 'undefined' && options.data instanceof FormData;
                return $.ajax({
                    url,
                    method: (options.method || 'GET').toUpperCase(),
                    data: options.data || undefined,
                    dataType: 'json',
                    processData: isFormData ? false : undefined,
                    contentType: isFormData ? false : undefined,
                });
            }

            function attachmentKey(file) {
                return [file.name, file.size, file.lastModified, file.type].join(':');
            }

            function renderPendingAttachments() {
                if (!draftAttachments) {
                    return;
                }

                if (!pendingAttachments.length) {
                    draftAttachments.innerHTML = '';
                    return;
                }

                draftAttachments.innerHTML = pendingAttachments.map((file, index) => `
                    <span class="communication-draft-chip">
                        <i class="${String(file.type || '').startsWith('image/') ? 'ri-image-line' : 'ri-attachment-2'}"></i>
                        <span>${escapeHtml(file.name)}</span>
                        <button type="button" data-remove-attachment-index="${index}" aria-label="Remove attachment">
                            <i class="ri-close-line"></i>
                        </button>
                    </span>
                `).join('');
            }

            function addPendingAttachments(fileList) {
                const seen = new Set(pendingAttachments.map((file) => attachmentKey(file)));

                Array.from(fileList || []).forEach((file) => {
                    const key = attachmentKey(file);
                    if (seen.has(key)) {
                        return;
                    }

                    pendingAttachments.push(file);
                    seen.add(key);
                });

                renderPendingAttachments();
            }

            function insertAtCursor(input, text) {
                if (!input) {
                    return;
                }

                const start = input.selectionStart ?? input.value.length;
                const end = input.selectionEnd ?? input.value.length;
                const value = input.value || '';
                input.value = `${value.slice(0, start)}${text}${value.slice(end)}`;
                const nextPosition = start + text.length;
                input.focus();
                input.setSelectionRange(nextPosition, nextPosition);
            }

            function hideEmojiPicker() {
                emojiPicker?.classList.add('d-none');
            }

            function toggleEmojiPicker() {
                const willShow = emojiPicker?.classList.contains('d-none');
                emojiPicker?.classList.toggle('d-none');

                if (willShow) {
                    renderEmojiPicker();
                    emojiSearchInput?.focus();
                }
            }

            function responsePayload(response) {
                if (Array.isArray(response?.data)) {
                    return response.data;
                }

                if (response?.data && typeof response.data === 'object' && 'data' in response.data) {
                    return response.data.data;
                }

                if (response && typeof response === 'object' && 'data' in response) {
                    return response.data;
                }

                return response;
            }

            function filteredConversations() {
                const query = (searchInput?.value || '').trim().toLowerCase();

                if (!query) {
                    return conversations;
                }

                return conversations.filter((conversation) => {
                    const haystack = [
                        conversationName(conversation),
                        conversationPreview(conversation),
                        conversation.type_label || '',
                    ].join(' ').toLowerCase();

                    return haystack.includes(query);
                });
            }

            function sortConversations() {
                conversations.sort((left, right) => {
                    const leftTime = new Date(left?.last_message_at || left?.updated_at || 0).getTime();
                    const rightTime = new Date(right?.last_message_at || right?.updated_at || 0).getTime();
                    return rightTime - leftTime;
                });
            }

            function rerenderSidebar() {
                sortConversations();
                renderConversations(filteredConversations());
                refreshActiveConversationMeta();
            }

            function upsertConversation(conversation) {
                if (!conversation?.id) {
                    return null;
                }

                const index = conversations.findIndex((item) => String(item.id) === String(conversation.id));
                if (index === -1) {
                    conversations.unshift(conversation);
                } else {
                    conversations[index] = {
                        ...conversations[index],
                        ...conversation,
                    };
                }

                sortConversations();

                return conversationById(conversation.id);
            }

            function updateConversationFromMessage(message) {
                if (!message?.conversation_id) {
                    return;
                }

                const existingConversation = conversationById(message.conversation_id);
                if (!existingConversation) {
                    return;
                }

                upsertConversation({
                    ...existingConversation,
                    latest_message: {
                        ...(existingConversation.latest_message || {}),
                        ...message,
                    },
                    last_message_at: message.created_at || existingConversation.last_message_at,
                    updated_at: message.created_at || existingConversation.updated_at,
                    is_archived: false,
                });
            }

            function uniqueUserCount(records, excludeUserId) {
                return (Array.isArray(records) ? records : [])
                    .map((item) => String(item?.user_id || ''))
                    .filter((userId, index, list) => userId && userId !== String(excludeUserId || '') && list.indexOf(userId) === index)
                    .length;
            }

            function recomputeMessageStatus(message) {
                const recipientsCount = Number(message?.recipients_count || 0);
                const deliveredToCount = uniqueUserCount(message?.deliveries, message?.sender_id);
                const readByCount = uniqueUserCount(message?.reads, message?.sender_id);
                let status = 'sent';

                if (recipientsCount > 0 && deliveredToCount >= recipientsCount) {
                    status = 'delivered';
                }

                if (recipientsCount > 0 && readByCount >= recipientsCount) {
                    status = 'read';
                }

                return {
                    ...message,
                    delivered_to_count: deliveredToCount,
                    read_by_count: readByCount,
                    status,
                };
            }

            function upsertCurrentMessage(message) {
                if (!message?.id) {
                    return;
                }

                const nextMessage = recomputeMessageStatus(message);
                const index = currentMessages.findIndex((item) => String(item.id) === String(nextMessage.id));

                if (index === -1) {
                    currentMessages.push(nextMessage);
                } else {
                    currentMessages[index] = recomputeMessageStatus({
                        ...currentMessages[index],
                        ...nextMessage,
                    });
                }

                currentMessages.sort((left, right) => new Date(left?.created_at || 0).getTime() - new Date(right?.created_at || 0)
                    .getTime());
            }

            function mergeMessageReceipts(messageId, key, records) {
                const index = currentMessages.findIndex((item) => String(item.id) === String(messageId));
                if (index === -1) {
                    return;
                }

                const existing = Array.isArray(currentMessages[index][key]) ? currentMessages[index][key] : [];
                const merged = [...existing];

                (Array.isArray(records) ? records : []).forEach((record) => {
                    const recordIndex = merged.findIndex((item) => String(item?.user_id || '') === String(record?.user_id || ''));
                    if (recordIndex === -1) {
                        merged.push(record);
                    } else {
                        merged[recordIndex] = {
                            ...merged[recordIndex],
                            ...record,
                        };
                    }
                });

                currentMessages[index] = recomputeMessageStatus({
                    ...currentMessages[index],
                    [key]: merged,
                });
            }

            async function refreshConversationSidebar() {
                const response = await requestJson(`${apiBase}/conversations`);
                const payload = responsePayload(response);
                conversations = Array.isArray(payload) ? payload : [];
                syncPresenceMap(conversations);
                renderConversations(filteredConversations());
            }

            async function loadConversations(preferredConversationId = null) {
                await refreshConversationSidebar();
                const filtered = filteredConversations();

                if (!filtered.length) {
                    compactMessengerView = 'list';
                    conversationTitle.textContent = 'No conversations';
                    conversationMeta.textContent = 'Create a new conversation to get started';
                    if (conversationAvatar) {
                        conversationAvatar.src = defaultAvatar;
                        conversationAvatar.alt = 'No conversations';
                    }
                    activeConversationId = null;
                    list.innerHTML =
                        '<div class="p-24 text-center text-secondary-light">No conversations found.</div>';
                    setComposerState(false);
                    syncCompactMessengerLayout();
                    return;
                }

                if (isCompactMessengerView() && !preferredConversationId && !activeConversationId) {
                    compactMessengerView = 'list';
                    showConversationPlaceholder();
                    syncCompactMessengerLayout();
                    return;
                }

                const desiredId = preferredConversationId || activeConversationId || (!isCompactMessengerView() ? filtered[0]
                    ?.id : null);

                if (desiredId && filtered.some((conversation) => String(conversation.id) === String(desiredId))) {
                    await loadMessages(desiredId);
                    return;
                }

                if (isCompactMessengerView()) {
                    compactMessengerView = 'list';
                    showConversationPlaceholder();
                } else if (filtered[0]?.id) {
                    await loadMessages(filtered[0].id);
                    return;
                }

                syncCompactMessengerLayout();
            }

            async function loadMessages(conversationId) {
                const conversation = conversationById(conversationId);
                if (!conversation) return;

                activeConversationId = conversationId;
                compactMessengerView = 'chat';
                syncCompactMessengerLayout();
                renderConversations(conversations.filter((item) => {
                    const query = (searchInput?.value || '').trim().toLowerCase();
                    if (!query) return true;
                    const haystack = [conversationName(item), conversationPreview(item), item
                        .type_label || ''
                    ].join(' ').toLowerCase();
                    return haystack.includes(query);
                }));

                conversationTitle.textContent = conversationName(conversation);
                if (conversationAvatar) {
                    conversationAvatar.src = conversationAvatarUrl(conversation);
                    conversationAvatar.alt = conversationName(conversation);
                }
                refreshActiveConversationMeta();
                setComposerState(true);

                const response = await requestJson(`${apiBase}/conversations/${conversationId}/messages`);
                const payload = responsePayload(response);
                const messages = Array.isArray(payload) ? payload.slice().reverse() : [];
                renderMessages(messages);

                try {
                    await requestJson(`${apiBase}/conversations/${conversationId}/read`, {
                        method: 'post'
                    });
                } catch (error) {}
            }

            async function createConversation(formData) {
                const response = await requestJson(`${apiBase}/conversations`, {
                    method: 'post',
                    data: formData,
                });

                return responsePayload(response) || null;
            }

            async function sendMessage(body) {
                if (!activeConversationId) return;

                const payload = new FormData();
                payload.append('body', body || '');
                pendingAttachments.forEach((file) => {
                    payload.append('attachments[]', file);
                });

                const response = await requestJson(`${apiBase}/conversations/${activeConversationId}/messages`, {
                    method: 'post',
                    data: payload,
                });

                return responsePayload(response) || null;
            }

            async function archiveConversation() {
                if (!activeConversationId) return;

                await requestJson(`${apiBase}/conversations/${activeConversationId}/archive`, {
                    method: 'post',
                });
            }

            conversationList.addEventListener('click', async function(event) {
                const button = event.target.closest('[data-conversation-id]');
                if (!button) return;

                await loadMessages(button.dataset.conversationId);
            });

            conversationMobileBackButton?.addEventListener('click', function() {
                compactMessengerView = 'list';
                syncCompactMessengerLayout();
            });

            form.addEventListener('submit', async function(event) {
                event.preventDefault();

                if (!activeConversationId) {
                    return;
                }

                const body = (sendInput.value || '').trim();
                if (!body && !pendingAttachments.length) {
                    return;
                }

                sendButton.disabled = true;

                try {
                    const message = await sendMessage(body);
                    playNotificationSound('outgoing');
                    sendInput.value = '';
                    autoResizeComposer();
                    pendingAttachments = [];
                    renderPendingAttachments();
                    if (attachmentInput) {
                        attachmentInput.value = '';
                    }
                    if (imageInput) {
                        imageInput.value = '';
                    }
                    if (message) {
                        upsertCurrentMessage(message);
                        updateConversationFromMessage(message);
                        renderMessages(currentMessages);
                        rerenderSidebar();
                    }
                } catch (error) {
                    Swal.fire('Error', error?.response?.data?.message ||
                        'Unable to send message right now.', 'error');
                } finally {
                    sendButton.disabled = false;
                }
            });

            draftAttachments?.addEventListener('click', function(event) {
                const button = event.target.closest('[data-remove-attachment-index]');
                if (!button) {
                    return;
                }

                const index = Number(button.dataset.removeAttachmentIndex);
                if (!Number.isInteger(index)) {
                    return;
                }

                pendingAttachments.splice(index, 1);
                renderPendingAttachments();
            });

            sendInput?.addEventListener('input', function() {
                autoResizeComposer();
            });

            sendInput?.addEventListener('keydown', function(event) {
                if (event.key !== 'Enter' || event.shiftKey) {
                    return;
                }

                event.preventDefault();
                if (!sendButton?.disabled) {
                    form?.requestSubmit();
                }
            });

            attachFileButton?.addEventListener('click', function() {
                attachmentInput?.click();
            });

            toggleEmojiPickerButton?.addEventListener('click', function(event) {
                event.stopPropagation();
                toggleEmojiPicker();
            });

            attachImageButton?.addEventListener('click', function() {
                imageInput?.click();
            });

            attachmentInput?.addEventListener('change', function() {
                addPendingAttachments(this.files);
                this.value = '';
            });

            imageInput?.addEventListener('change', function() {
                addPendingAttachments(this.files);
                this.value = '';
            });

            emojiPicker?.addEventListener('click', function(event) {
                const button = event.target.closest('[data-emoji]');
                if (!button) {
                    return;
                }

                insertAtCursor(sendInput, button.dataset.emoji || '');
                hideEmojiPicker();
            });

            emojiSearchInput?.addEventListener('input', function() {
                renderEmojiPicker();
            });

            document.addEventListener('click', function(event) {
                if (emojiPicker?.contains(event.target) || toggleEmojiPickerButton?.contains(event.target)) {
                    return;
                }

                hideEmojiPicker();
            });

            archiveButton?.addEventListener('click', async function() {
                if (!activeConversationId) {
                    return;
                }

                const result = await Swal.fire({
                    text: 'Archive this conversation?',
                    showCancelButton: true,
                    confirmButtonText: 'Archive',
                });

                if (!result.isConfirmed) {
                    return;
                }

                try {
                    await archiveConversation();
                    await loadConversations();
                    Swal.fire('Success', 'Conversation archived successfully.', 'success');
                } catch (error) {
                    Swal.fire('Error', error?.response?.data?.message || 'Unable to archive conversation.',
                        'error');
                }
            });

            searchInput?.addEventListener('input', function() {
                renderConversations(conversations.filter((conversation) => {
                    const query = (searchInput.value || '').trim().toLowerCase();
                    if (!query) return true;
                    const haystack = [conversationName(conversation), conversationPreview(
                        conversation), conversation.type_label || ''].join(' ').toLowerCase();
                    return haystack.includes(query);
                }));
            });

            newConversationForm?.addEventListener('submit', async function(event) {
                event.preventDefault();

                const data = new FormData(newConversationForm);
                const selectedParticipants = Array.from(newConversationForm.querySelector(
                        '[name="participant_ids[]"]').selectedOptions)
                    .map((option) => option.value)
                    .filter(Boolean);

                if (!selectedParticipants.length) {
                    Swal.fire('Error', 'Please select at least one participant.', 'error');
                    return;
                }

                const payload = {
                    type: data.get('type'),
                    title: data.get('title') || null,
                    participant_ids: selectedParticipants,
                };

                try {
                    const conversation = await createConversation(payload);
                    const modal = document.getElementById('newConversationModal');
                    if (modal && bootstrapModal) {
                        bootstrapModal.getInstance(modal)?.hide();
                    }
                    newConversationForm.reset();
                    await loadConversations(conversation?.id || null);
                    Swal.fire('Success', 'Conversation created successfully.', 'success');
                } catch (error) {
                    Swal.fire('Error', error?.response?.data?.message || 'Unable to create conversation.',
                        'error');
                }
            });

            window.addEventListener('communication:entity.changed', async function(event) {
                const payload = event.detail || {};

                if (payload.entity === 'conversation' && payload.action === 'created') {
                    await loadConversations(payload.id || null);
                    return;
                }

                if (payload.entity === 'message' && payload.action === 'created') {
                    const isOwnMessage = String(payload.sender_id || payload.actor_user_id || '') ===
                        String(
                            currentUserId);

                    if (!isOwnMessage) {
                        playNotificationSound('incoming');
                    }

                    updateConversationFromMessage(payload);
                    rerenderSidebar();

                    if (String(payload.conversation_id || '') === String(activeConversationId || '')) {
                        upsertCurrentMessage(payload);
                        renderMessages(currentMessages);
                    } else {
                        refreshActiveConversationMeta();
                    }

                    return;
                }

                if (payload.entity === 'message_read' && String(payload.actor_user_id || '') === String(
                        currentUserId)) {
                    return;
                }

                if ((payload.entity === 'message_delivery' || payload.entity === 'message_read') && String(payload
                        .conversation_id || '') === String(activeConversationId || '')) {
                    if (payload.entity === 'message_delivery') {
                        mergeMessageReceipts(payload.message_id, 'deliveries', payload.deliveries || []);
                    } else {
                        const readsByMessageId = {};
                        (Array.isArray(payload.reads) ? payload.reads : []).forEach((read) => {
                            const messageId = String(read?.message_id || '');
                            if (!messageId) {
                                return;
                            }

                            readsByMessageId[messageId] = readsByMessageId[messageId] || [];
                            readsByMessageId[messageId].push(read);
                        });

                        Object.entries(readsByMessageId).forEach(([messageId, reads]) => {
                            mergeMessageReceipts(messageId, 'reads', reads);
                        });
                    }

                    renderMessages(currentMessages);
                    return;
                }

                if (payload.entity === 'conversation' && payload.action === 'archived') {
                    await loadConversations();
                }
            });

            window.addEventListener('core:auth.activity', function(event) {
                const payload = event.detail || {};
                const userId = payload.user_id || '';

                if (!userId) {
                    return;
                }

                if (payload.action === 'login' || payload.action === 'register') {
                    setUserOnlineStatus(userId, true);
                }

                if (payload.action === 'logout') {
                    setUserOnlineStatus(userId, false);
                }

                renderConversations(conversations.filter((conversation) => {
                    const query = (searchInput?.value || '').trim().toLowerCase();
                    if (!query) return true;
                    const haystack = [conversationName(conversation), conversationPreview(
                            conversation),
                        conversation.type_label || ''
                    ].join(' ').toLowerCase();
                    return haystack.includes(query);
                }));
                refreshActiveConversationMeta();
            });

            window.addEventListener('resize', function() {
                syncCompactMessengerLayout();
            });

            primeNotificationAudio();
            startPresenceHeartbeat();
            autoResizeComposer();
            syncCompactMessengerLayout();

            loadConversations().catch((error) => {
                conversationList.innerHTML =
                    '<div class="p-24 text-center text-danger-600">Unable to load conversations.</div>';
                list.innerHTML = '<div class="p-24 text-center text-danger-600">Unable to load messages.</div>';
                console.error(error);
            });
        })();
    </script>
@endpush
