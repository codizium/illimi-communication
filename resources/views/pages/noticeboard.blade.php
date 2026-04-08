@extends('layouts.app')

@section('content')
    @php
        $noticeGroups = $notices
            ->groupBy(fn ($notice) => optional($notice->published_at ?? $notice->created_at)->format('F Y') ?: 'Unscheduled')
            ->map(fn ($items, $label) => [
                'label' => $label,
                'count' => $items->count(),
                'summary' => $items->take(3)->map(fn ($notice) => [
                    'title' => $notice->title,
                    'date' => optional($notice->published_at ?? $notice->created_at)->format('d M'),
                ])->values(),
            ])
            ->values();
    @endphp

    <div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">Notice Board</h1>
            <div>
                <a href="{{ route('home') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <span class="text-secondary-light">/ Notice Board</span>
            </div>
        </div>
        <button type="button" class="btn btn-primary-600 d-flex align-items-center gap-6" data-bs-toggle="modal"
            data-bs-target="#createNoticeModal">
            <span class="d-flex text-md"><i class="ri-add-large-line"></i></span>
            Add Notice
        </button>
    </div>

    <div class="mt-24" data-communication-notices data-api-base="{{ $apiBase }}"
        data-current-user-id="{{ auth()->id() }}">
        <div class="row gy-4">
            <div class="col-xxl-4 col-lg-5">
                <div class="card h-100 p-0">
                    <div class="card-body p-24">
                        <div class="d-flex align-items-center justify-content-between gap-3 mb-16">
                            <div>
                                <h6 class="mb-4">Published Notices</h6>
                                <p class="text-secondary-light text-sm mb-0">{{ $notices->count() }} total notice(s)</p>
                            </div>
                            <span class="badge bg-primary-50 text-primary-600">Live Data</span>
                        </div>

                        <div class="mt-8" id="communicationNoticeList">
                            @forelse ($notices as $notice)
                                @php
                                    $publishedAt = $notice->published_at ?? $notice->created_at;
                                    $accent = $notice->is_pinned ? 'warning' : 'primary';
                                @endphp

                                <div
                                    class="event-item d-flex align-items-start justify-content-between gap-4 pb-16 mb-16 border border-start-0 border-end-0 border-top-0">
                                    <div class="pe-8">
                                        <div class="d-flex align-items-center gap-10">
                                            <span
                                                class="w-12-px h-12-px bg-{{ $accent }}-600 rounded-circle fw-medium"></span>
                                            <span class="text-secondary-light">{{ optional($publishedAt)->format('D, d M Y, h:i A') }}</span>
                                        </div>
                                        <span
                                            class="text-primary-light fw-semibold text-md mt-4 d-inline-block">{{ $notice->title }}</span>
                                        <p class="text-secondary-light text-sm mb-0 mt-6">
                                            {{ \Illuminate\Support\Str::limit($notice->description, 140) }}
                                        </p>
                                        @if ($notice->creator?->name)
                                            <p class="text-secondary-light text-sm mb-0 mt-6">
                                                <i class="ri-user-3-line me-4"></i>{{ $notice->creator->name }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="text-end">
                                        @if ($notice->is_pinned)
                                            <span class="badge bg-warning-50 text-warning-600">Pinned</span>
                                        @else
                                            <span class="badge bg-primary-50 text-primary-600">Published</span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-32">
                                    <div
                                        class="w-64-px h-64-px bg-primary-50 text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center text-2xl mb-12">
                                        <i class="ri-notification-3-line"></i>
                                    </div>
                                    <h6 class="mb-8">No notices yet</h6>
                                    <p class="text-secondary-light mb-16">Create your first notice to start populating
                                        this board.</p>
                                    <button type="button" class="btn btn-primary-600" data-bs-toggle="modal"
                                        data-bs-target="#createNoticeModal">Create Notice</button>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-8 col-lg-7">
                <div class="card h-100 p-0">
                    <div class="card-body p-24">
                        <div class="border radius-12 p-24 bg-base">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-12 mb-20">
                                <div>
                                    <h6 class="mb-6">Notice Summary</h6>
                                    <p class="text-secondary-light mb-0">Real notice records grouped by month for quick
                                        visibility.</p>
                                </div>
                                <span class="badge bg-primary-50 text-primary-600">Communication Module</span>
                            </div>
                            <div class="row g-3" id="communicationNoticeGroups">
                                @forelse ($noticeGroups as $group)
                                    <div class="col-md-6 col-xl-4">
                                        <div class="border radius-8 p-16 h-100">
                                            <div class="d-flex align-items-center justify-content-between mb-10">
                                                <span class="fw-semibold text-primary-light">{{ $group['label'] }}</span>
                                                <span class="text-secondary-light text-sm">{{ $group['count'] }}
                                                    notice(s)</span>
                                            </div>
                                            @if (count($group['summary']))
                                                <div class="d-flex flex-column gap-8">
                                                    @foreach ($group['summary'] as $item)
                                                        <div
                                                            class="d-flex align-items-center justify-content-between gap-8">
                                                            <span
                                                                class="text-secondary-light text-sm">{{ $item['date'] }}</span>
                                                            <span
                                                                class="text-primary-light text-sm fw-medium text-end">{{ $item['title'] }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="text-secondary-light text-sm mb-0">No items in this period.</p>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <div class="border radius-8 p-24 text-center">
                                            <p class="text-secondary-light mb-0">Month summary will appear once notices are
                                                created.</p>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createNoticeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form id="communicationNoticeForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Create Notice</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Title</label>
                                <input type="text" class="form-control" name="title" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Publish At</label>
                                <input type="datetime-local" class="form-control" name="published_at">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" rows="5" name="description" required></textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" name="is_pinned"
                                        id="noticePinned">
                                    <label class="form-check-label" for="noticePinned">
                                        Pin this notice
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary-600">Publish Notice</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const root = document.querySelector('[data-communication-notices]');
            const form = document.getElementById('communicationNoticeForm');
            const noticeList = document.getElementById('communicationNoticeList');
            const noticeGroups = document.getElementById('communicationNoticeGroups');

            if (!root || !form || !noticeList || !noticeGroups || typeof $ === 'undefined') {
                return;
            }

            const apiBase = root.dataset.apiBase || '/api/v1/communication';
            const currentUserId = root.dataset.currentUserId || '';
            const bootstrapModal = window.bootstrap?.Modal;
            let notices = @json($noticePayload);

            function requestJson(url, options = {}) {
                return $.ajax({
                    url,
                    method: (options.method || 'GET').toUpperCase(),
                    data: options.data || undefined,
                    dataType: 'json',
                });
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

            function escapeHtml(value) {
                return $('<div>').text(value || '').html();
            }

            function formatDateTime(value) {
                if (!value) {
                    return 'Unscheduled';
                }

                const date = new Date(value);
                if (Number.isNaN(date.getTime())) {
                    return 'Unscheduled';
                }

                return date.toLocaleString([], {
                    weekday: 'short',
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                });
            }

            function monthLabel(value) {
                const date = value ? new Date(value) : null;
                if (!date || Number.isNaN(date.getTime())) {
                    return 'Unscheduled';
                }

                return date.toLocaleString([], {
                    month: 'long',
                    year: 'numeric',
                });
            }

            function groupNotices(items) {
                return items.reduce((carry, item) => {
                    const label = monthLabel(item?.published_at || item?.created_at);
                    carry[label] = carry[label] || [];
                    carry[label].push(item);
                    return carry;
                }, {});
            }

            function renderNoticeList(items) {
                if (!items.length) {
                    noticeList.innerHTML = `
                        <div class="text-center py-32">
                            <div class="w-64-px h-64-px bg-primary-50 text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center text-2xl mb-12">
                                <i class="ri-notification-3-line"></i>
                            </div>
                            <h6 class="mb-8">No notices yet</h6>
                            <p class="text-secondary-light mb-16">Create your first notice to start populating this board.</p>
                            <button type="button" class="btn btn-primary-600" data-bs-toggle="modal" data-bs-target="#createNoticeModal">Create Notice</button>
                        </div>
                    `;
                    return;
                }

                noticeList.innerHTML = items.map((item) => {
                    const accent = item.is_pinned ? 'warning' : 'primary';
                    return `
                        <div class="event-item d-flex align-items-start justify-content-between gap-4 pb-16 mb-16 border border-start-0 border-end-0 border-top-0">
                            <div class="pe-8">
                                <div class="d-flex align-items-center gap-10">
                                    <span class="w-12-px h-12-px bg-${accent}-600 rounded-circle fw-medium"></span>
                                    <span class="text-secondary-light">${formatDateTime(item.published_at || item.created_at)}</span>
                                </div>
                                <span class="text-primary-light fw-semibold text-md mt-4 d-inline-block">${escapeHtml(item.title)}</span>
                                <p class="text-secondary-light text-sm mb-0 mt-6">${escapeHtml(item.description)}</p>
                                ${item.creator?.name ? `<p class="text-secondary-light text-sm mb-0 mt-6"><i class="ri-user-3-line me-4"></i>${escapeHtml(item.creator.name)}</p>` : ''}
                            </div>
                            <div class="text-end">
                                ${item.is_pinned ? '<span class="badge bg-warning-50 text-warning-600">Pinned</span>' : '<span class="badge bg-primary-50 text-primary-600">Published</span>'}
                            </div>
                        </div>
                    `;
                }).join('');
            }

            function renderNoticeGroups(items) {
                const groups = groupNotices(items);
                const entries = Object.entries(groups);

                if (!entries.length) {
                    noticeGroups.innerHTML = `
                        <div class="col-12">
                            <div class="border radius-8 p-24 text-center">
                                <p class="text-secondary-light mb-0">Month summary will appear once notices are created.</p>
                            </div>
                        </div>
                    `;
                    return;
                }

                noticeGroups.innerHTML = entries.map(([label, rows]) => `
                    <div class="col-md-6 col-xl-4">
                        <div class="border radius-8 p-16 h-100">
                            <div class="d-flex align-items-center justify-content-between mb-10">
                                <span class="fw-semibold text-primary-light">${escapeHtml(label)}</span>
                                <span class="text-secondary-light text-sm">${rows.length} notice(s)</span>
                            </div>
                            <div class="d-flex flex-column gap-8">
                                ${rows.slice(0, 3).map((item) => `
                                    <div class="d-flex align-items-center justify-content-between gap-8">
                                        <span class="text-secondary-light text-sm">${escapeHtml((item.published_at || item.created_at) ? new Date(item.published_at || item.created_at).toLocaleDateString([], { day: '2-digit', month: 'short' }) : 'N/A')}</span>
                                        <span class="text-primary-light text-sm fw-medium text-end">${escapeHtml(item.title)}</span>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    </div>
                `).join('');
            }

            function renderAll() {
                notices = (notices || []).slice().sort((left, right) => {
                    if (Boolean(left?.is_pinned) !== Boolean(right?.is_pinned)) {
                        return Boolean(right?.is_pinned) - Boolean(left?.is_pinned);
                    }

                    return new Date(right?.published_at || right?.created_at || 0).getTime() - new Date(left?.published_at || left?.created_at || 0).getTime();
                });

                renderNoticeList(notices);
                renderNoticeGroups(notices);
            }

            async function loadNotices() {
                const response = await requestJson(`${apiBase}/notices`);
                const payload = responsePayload(response);
                notices = Array.isArray(payload) ? payload : [];
                renderAll();
            }

            form.addEventListener('submit', async function(event) {
                event.preventDefault();

                const submitButton = form.querySelector('button[type="submit"]');
                const payload = {
                    title: form.elements.title.value,
                    description: form.elements.description.value,
                    published_at: form.elements.published_at.value || null,
                    is_pinned: form.elements.is_pinned.checked ? 1 : 0,
                };

                submitButton.disabled = true;

                try {
                    await requestJson(`${apiBase}/notices`, {
                        method: 'post',
                        data: payload,
                    });

                    const modal = document.getElementById('createNoticeModal');
                    if (modal && bootstrapModal) {
                        bootstrapModal.getInstance(modal)?.hide();
                    }

                    form.reset();
                    await loadNotices();
                    Swal.fire('Success', 'Notice published successfully.', 'success');
                } catch (error) {
                    Swal.fire('Error', error?.responseJSON?.message || 'Unable to publish notice right now.',
                        'error');
                } finally {
                    submitButton.disabled = false;
                }
            });

            window.addEventListener('communication:entity.changed', async function(event) {
                const payload = event.detail || {};

                if (payload.entity !== 'notice' || payload.action !== 'created') {
                    return;
                }

                await loadNotices();
            });

            renderAll();
        })();
    </script>
@endpush
