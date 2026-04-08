@extends('layouts.app')

@section('content')
    <div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <span class="fw-semibold mb-4 h6 text-primary-light d-block">Event</span>
            <div>
                <a href="{{ route('home') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <span class="text-secondary-light">/ Event</span>
            </div>
        </div>
        <button type="button" class="btn btn-primary-600 d-flex align-items-center gap-6" data-bs-toggle="modal"
            data-bs-target="#createEventModal">
            <span class="d-flex text-md"><i class="ri-add-large-line"></i></span>
            Add Event
        </button>
    </div>

    <div class="mt-24" data-communication-events data-api-base="{{ $apiBase }}"
        data-current-user-id="{{ auth()->id() }}">
        <div class="row gy-4">
            <div class="col-xxl-4 col-lg-5">
                <div class="card h-100 p-0">
                    <div class="card-body p-24">
                        <div class="d-flex align-items-center justify-content-between gap-3 mb-16">
                            <div>
                                <h6 class="mb-4">Upcoming & Scheduled</h6>
                                <p class="text-secondary-light text-sm mb-0">{{ $events->count() }} total event(s)</p>
                            </div>
                            <span class="badge bg-primary-50 text-primary-600">Live Data</span>
                        </div>

                        <div class="mt-8" id="communicationEventList">
                            @forelse ($events as $event)
                                @php
                                    $startsAt = $event->starts_at;
                                    $endsAt = $event->ends_at;
                                    $isPast = $startsAt ? $startsAt->isPast() && (!$endsAt || $endsAt->isPast()) : false;
                                    $accent = $isPast ? 'secondary' : ($startsAt && $startsAt->isToday() ? 'warning' : 'success');
                                    $timeLabel = $startsAt
                                        ? $startsAt->format('D, d M Y, h:i A')
                                        : 'Unscheduled';

                                    if ($startsAt && $endsAt) {
                                        $timeLabel .= ' - ' . $endsAt->format($startsAt->isSameDay($endsAt) ? 'h:i A' : 'D, d M Y, h:i A');
                                    }
                                @endphp

                                <div
                                    class="event-item d-flex align-items-start justify-content-between gap-4 pb-16 mb-16 border border-start-0 border-end-0 border-top-0">
                                    <div class="pe-8">
                                        <div class="d-flex align-items-center gap-10">
                                            <span
                                                class="w-12-px h-12-px bg-{{ $accent }}-600 rounded-circle fw-medium"></span>
                                            <span class="text-secondary-light">{{ $timeLabel }}</span>
                                        </div>
                                        <span
                                            class="text-primary-light fw-semibold text-md mt-4 d-inline-block">{{ $event->title }}</span>
                                        @if ($event->location)
                                            <p class="text-secondary-light text-sm mb-0 mt-6">
                                                <i class="ri-map-pin-line me-4"></i>{{ $event->location }}
                                            </p>
                                        @endif
                                        @if ($event->description)
                                            <p class="text-secondary-light text-sm mb-0 mt-6">
                                                {{ \Illuminate\Support\Str::limit($event->description, 120) }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="text-end">
                                        <span
                                            class="badge bg-{{ $accent }}-50 text-{{ $accent }}-600">{{ $isPast ? 'Past' : ($startsAt && $startsAt->isToday() ? 'Today' : 'Scheduled') }}</span>
                                        @if ($event->allow_rsvp)
                                            <div class="mt-8">
                                                <span class="badge bg-info-50 text-info-600">RSVP On</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-32">
                                    <div class="w-64-px h-64-px bg-primary-50 text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center text-2xl mb-12">
                                        <i class="ri-calendar-event-line"></i>
                                    </div>
                                    <h6 class="mb-8">No events yet</h6>
                                    <p class="text-secondary-light mb-16">Create your first communication event to start
                                        populating this page.</p>
                                    <button type="button" class="btn btn-primary-600" data-bs-toggle="modal"
                                        data-bs-target="#createEventModal">Create Event</button>
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
                                    <h6 class="mb-6">Event Calendar Summary</h6>
                                    <p class="text-secondary-light mb-0">Real event records grouped by month for quick
                                        visibility.</p>
                                </div>
                                <span class="badge bg-primary-50 text-primary-600">Communication Module</span>
                            </div>
                            <div class="row g-3" id="communicationEventCalendarGroups">
                                @forelse ($calendarGroups as $group)
                                    <div class="col-md-6 col-xl-4">
                                        <div class="border radius-8 p-16 h-100">
                                            <div class="d-flex align-items-center justify-content-between mb-10">
                                                <span class="fw-semibold text-primary-light">{{ $group['label'] }}</span>
                                                <span class="text-secondary-light text-sm">{{ $group['count'] }}
                                                    event(s)</span>
                                            </div>
                                            @if (count($group['summary']))
                                                <div class="d-flex flex-column gap-8">
                                                    @foreach ($group['summary'] as $item)
                                                        <div class="d-flex align-items-center justify-content-between gap-8">
                                                            <span class="text-secondary-light text-sm">{{ $item['date'] }}</span>
                                                            <span class="text-primary-light text-sm fw-medium text-end">{{ $item['title'] }}</span>
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
                                            <p class="text-secondary-light mb-0">Month summary will appear once events are
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

    <div class="modal fade" id="createEventModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form id="communicationEventForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Create Event</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Title</label>
                                <input type="text" class="form-control" name="title" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Location</label>
                                <input type="text" class="form-control" name="location">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Starts At</label>
                                <input type="datetime-local" class="form-control" name="starts_at" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ends At</label>
                                <input type="datetime-local" class="form-control" name="ends_at">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Max Attendees</label>
                                <input type="number" min="1" class="form-control" name="max_attendees">
                            </div>
                            <div class="col-md-6 d-flex align-items-center">
                                <div class="form-check mt-24">
                                    <input class="form-check-input" type="checkbox" value="1" name="allow_rsvp"
                                        id="eventAllowRsvp">
                                    <label class="form-check-label" for="eventAllowRsvp">
                                        Allow RSVP
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" rows="4" name="description"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary-600">Save Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const root = document.querySelector('[data-communication-events]');
            const form = document.getElementById('communicationEventForm');
            const eventList = document.getElementById('communicationEventList');
            const calendarGroups = document.getElementById('communicationEventCalendarGroups');

            if (!root || !form || !eventList || !calendarGroups || typeof $ === 'undefined') {
                return;
            }

            const apiBase = root.dataset.apiBase || '/api/v1/communication';
            const currentUserId = root.dataset.currentUserId || '';
            const bootstrapModal = window.bootstrap?.Modal;
            let events = @json($eventPayload);

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

            function formatDateLabel(value) {
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

            function formatRangeLabel(item) {
                const startsAt = item?.starts_at ? new Date(item.starts_at) : null;
                const endsAt = item?.ends_at ? new Date(item.ends_at) : null;

                if (!startsAt || Number.isNaN(startsAt.getTime())) {
                    return 'Unscheduled';
                }

                let label = formatDateLabel(item.starts_at);

                if (endsAt && !Number.isNaN(endsAt.getTime())) {
                    const sameDay = startsAt.toDateString() === endsAt.toDateString();
                    label += ` - ${endsAt.toLocaleString([], sameDay ? {
                        hour: '2-digit',
                        minute: '2-digit',
                    } : {
                        weekday: 'short',
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                    })}`;
                }

                return label;
            }

            function isPastEvent(item) {
                const startsAt = item?.starts_at ? new Date(item.starts_at) : null;
                const endsAt = item?.ends_at ? new Date(item.ends_at) : null;
                const now = new Date();

                if (!startsAt || Number.isNaN(startsAt.getTime())) {
                    return false;
                }

                return endsAt && !Number.isNaN(endsAt.getTime()) ? endsAt < now : startsAt < now;
            }

            function isToday(value) {
                const date = value ? new Date(value) : null;
                if (!date || Number.isNaN(date.getTime())) {
                    return false;
                }

                const now = new Date();
                return date.getFullYear() === now.getFullYear() && date.getMonth() === now.getMonth() && date
                    .getDate() === now.getDate();
            }

            function accentForEvent(item) {
                if (isPastEvent(item)) {
                    return 'secondary';
                }

                return isToday(item?.starts_at) ? 'warning' : 'success';
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

            function groupEvents(items) {
                return items.reduce((carry, item) => {
                    const label = monthLabel(item?.starts_at);
                    carry[label] = carry[label] || [];
                    carry[label].push(item);
                    return carry;
                }, {});
            }

            function renderEventList(items) {
                if (!items.length) {
                    eventList.innerHTML = `
                        <div class="text-center py-32">
                            <div class="w-64-px h-64-px bg-primary-50 text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center text-2xl mb-12">
                                <i class="ri-calendar-event-line"></i>
                            </div>
                            <h6 class="mb-8">No events yet</h6>
                            <p class="text-secondary-light mb-16">Create your first communication event to start populating this page.</p>
                            <button type="button" class="btn btn-primary-600" data-bs-toggle="modal" data-bs-target="#createEventModal">Create Event</button>
                        </div>
                    `;
                    return;
                }

                eventList.innerHTML = items.map((item) => {
                    const accent = accentForEvent(item);
                    const status = isPastEvent(item) ? 'Past' : (isToday(item?.starts_at) ? 'Today' : 'Scheduled');
                    return `
                        <div class="event-item d-flex align-items-start justify-content-between gap-4 pb-16 mb-16 border border-start-0 border-end-0 border-top-0">
                            <div class="pe-8">
                                <div class="d-flex align-items-center gap-10">
                                    <span class="w-12-px h-12-px bg-${accent}-600 rounded-circle fw-medium"></span>
                                    <span class="text-secondary-light">${formatRangeLabel(item)}</span>
                                </div>
                                <span class="text-primary-light fw-semibold text-md mt-4 d-inline-block">${$('<div>').text(item.title || '').html()}</span>
                                ${item.location ? `<p class="text-secondary-light text-sm mb-0 mt-6"><i class="ri-map-pin-line me-4"></i>${$('<div>').text(item.location).html()}</p>` : ''}
                                ${item.description ? `<p class="text-secondary-light text-sm mb-0 mt-6">${$('<div>').text(item.description).html()}</p>` : ''}
                            </div>
                            <div class="text-end">
                                <span class="badge bg-${accent}-50 text-${accent}-600">${status}</span>
                                ${item.allow_rsvp ? '<div class="mt-8"><span class="badge bg-info-50 text-info-600">RSVP On</span></div>' : ''}
                            </div>
                        </div>
                    `;
                }).join('');
            }

            function renderCalendarGroups(items) {
                const groups = groupEvents(items);
                const entries = Object.entries(groups);

                if (!entries.length) {
                    calendarGroups.innerHTML = `
                        <div class="col-12">
                            <div class="border radius-8 p-24 text-center">
                                <p class="text-secondary-light mb-0">Month summary will appear once events are created.</p>
                            </div>
                        </div>
                    `;
                    return;
                }

                calendarGroups.innerHTML = entries.map(([label, rows]) => `
                    <div class="col-md-6 col-xl-4">
                        <div class="border radius-8 p-16 h-100">
                            <div class="d-flex align-items-center justify-content-between mb-10">
                                <span class="fw-semibold text-primary-light">${$('<div>').text(label).html()}</span>
                                <span class="text-secondary-light text-sm">${rows.length} event(s)</span>
                            </div>
                            <div class="d-flex flex-column gap-8">
                                ${rows.slice(0, 3).map((item) => `
                                    <div class="d-flex align-items-center justify-content-between gap-8">
                                        <span class="text-secondary-light text-sm">${item.starts_at ? new Date(item.starts_at).toLocaleDateString([], { day: '2-digit', month: 'short' }) : 'N/A'}</span>
                                        <span class="text-primary-light text-sm fw-medium text-end">${$('<div>').text(item.title || '').html()}</span>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    </div>
                `).join('');
            }

            function renderAll() {
                const sorted = (events || []).slice().sort((left, right) => {
                    return new Date(left?.starts_at || 0).getTime() - new Date(right?.starts_at || 0).getTime();
                });

                events = sorted;
                renderEventList(sorted);
                renderCalendarGroups(sorted);
            }

            async function loadEvents() {
                const response = await requestJson(`${apiBase}/events`);
                const payload = responsePayload(response);
                events = Array.isArray(payload) ? payload : [];
                renderAll();
            }

            form.addEventListener('submit', async function(event) {
                event.preventDefault();

                const submitButton = form.querySelector('button[type="submit"]');
                const payload = {
                    title: form.elements.title.value,
                    description: form.elements.description.value || null,
                    starts_at: form.elements.starts_at.value,
                    ends_at: form.elements.ends_at.value || null,
                    location: form.elements.location.value || null,
                    max_attendees: form.elements.max_attendees.value || null,
                    allow_rsvp: form.elements.allow_rsvp.checked ? 1 : 0,
                };

                submitButton.disabled = true;

                try {
                    await requestJson(`${apiBase}/events`, {
                        method: 'post',
                        data: payload,
                    });

                    const modal = document.getElementById('createEventModal');
                    if (modal && bootstrapModal) {
                        bootstrapModal.getInstance(modal)?.hide();
                    }

                    form.reset();
                    await loadEvents();
                    Swal.fire('Success', 'Event created successfully.', 'success');
                } catch (error) {
                    Swal.fire('Error', error?.responseJSON?.message || 'Unable to create event right now.',
                        'error');
                } finally {
                    submitButton.disabled = false;
                }
            });

            window.addEventListener('communication:entity.changed', async function(event) {
                const payload = event.detail || {};

                if (payload.entity !== 'event' || payload.action !== 'created') {
                    return;
                }

                await loadEvents();
            });

            renderAll();
        })();
    </script>
@endpush
