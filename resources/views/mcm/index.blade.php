@extends('layouts.app')

@php $pageTitle = 'Marketing Campaign Management'; @endphp

{{--
    NOTE: this page depends on the teammate's custom stylesheet (style.css),
    which hasn't been shared yet. Once you get it, save it as:
        public/css/mcm.css
    and this page will pick it up automatically via the @push('styles') below.
    Until then, this page will show correct DATA but with little/no visual
    styling, since none of the class names here (card, badge, roi-bar-fill,
    etc.) are Tailwind classes — they only mean something once mcm.css exists.
--}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/mcm.css') }}">
@endpush

@section('content')
<div class="mcm-page">

    @if (session('status'))
        <div class="flash-status">{{ session('status') }}</div>
    @endif

    <div class="page-columns">

        <div class="col-left">

            <!-- Create & Schedule Campaigns -->
            <div class="card">
                <div class="card-header">
                    <h2>Create &amp; Schedule Campaigns</h2>
                    <a href="{{ route('campaigns.create') }}" class="btn-primary">+ New Campaign</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Campaign Name</th>
                            <th>Channel</th>
                            <th>Type</th>
                            <th>Schedule</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($campaigns as $campaign)
                            <tr id="campaign-row-{{ $campaign->id }}">
                                <td>{{ $campaign->name }}</td>
                                <td><span class="channel-cell">{{ $campaign->channel }}</span></td>
                                <td>{{ $campaign->type }}</td>
                                <td>{{ \Carbon\Carbon::parse($campaign->send_date)->format('M j, Y') }}</td>
                                <td><span class="badge {{ $campaign->status }}">{{ ucfirst($campaign->status) }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">No campaigns yet. Click "+ New Campaign" to create one.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Lead Management -->
            <div class="card">
                <div class="card-header"><h2>Lead Management</h2></div>
                <div class="lead-stats">
                    <div class="lead-stat">
                        <div class="label">New Leads</div>
                        <div class="value">350</div>
                        <div class="delta">+15%</div>
                    </div>
                    <div class="lead-stat">
                        <div class="label">Contacted</div>
                        <div class="value">200</div>
                        <div class="delta">+10%</div>
                    </div>
                    <div class="lead-stat">
                        <div class="label">Qualified</div>
                        <div class="value">150</div>
                        <div class="delta">+6%</div>
                    </div>
                    <div class="lead-stat">
                        <div class="label">Converted</div>
                        <div class="value">75</div>
                        <div class="delta">+9%</div>
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Lead</th>
                            <th>Campaign</th>
                            <th>Source</th>
                            <th>Status</th>
                            <th>Date Captured</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Jean Dela Cruz</td>
                            <td>Summer Sale Blast</td>
                            <td><span class="source-cell">Email</span></td>
                            <td><span class="badge new">New</span></td>
                            <td>May 15, 2026</td>
                        </tr>
                        <tr>
                            <td>Maria Santos</td>
                            <td>New Product SMS</td>
                            <td><span class="source-cell">SMS</span></td>
                            <td><span class="badge contacted">Contacted</span></td>
                            <td>May 24, 2026</td>
                        </tr>
                        <tr>
                            <td>Kevin Reyes</td>
                            <td>Weekend Flashsale</td>
                            <td><span class="source-cell">TikTok</span></td>
                            <td><span class="badge contacted">Contacted</span></td>
                            <td>June 12, 2026</td>
                        </tr>
                        <tr>
                            <td>Ana Garcia</td>
                            <td>Follow us on Instagram!</td>
                            <td><span class="source-cell">Instagram</span></td>
                            <td><span class="badge qualified">Qualified</span></td>
                            <td>May 29, 2026</td>
                        </tr>
                        <tr>
                            <td>Luis Mendoza</td>
                            <td>New Product SMS</td>
                            <td><span class="source-cell">SMS</span></td>
                            <td><span class="badge qualified">Qualified</span></td>
                            <td>June 23, 2026</td>
                        </tr>
                        <tr>
                            <td>Sofia Lopez</td>
                            <td>Summer Sale Blast</td>
                            <td><span class="source-cell">Email</span></td>
                            <td><span class="badge qualified">Qualified</span></td>
                            <td>May 19, 2026</td>
                        </tr>
                        <tr>
                            <td>Mark Virgen</td>
                            <td>New Product SMS</td>
                            <td><span class="source-cell">SMS</span></td>
                            <td><span class="badge qualified">Qualified</span></td>
                            <td>May 27, 2026</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div><!-- /col-left -->

        <div class="col-right">

            <!-- Track Campaign Performance & ROI -->
            <div class="card">
                <div class="card-header">
                    <h2>Track Campaign Performance &amp; ROI</h2>
                </div>

                <div class="stat-row">
                    <div class="stat-pill">
                        <div class="stat-icon red">&#128176;</div>
                        <div class="stat-text">
                            <div class="label">Total Campaigns</div>
                            <div class="value">{{ $campaigns->count() }}</div>
                            <div class="delta">+15% from last month</div>
                        </div>
                    </div>
                    <div class="stat-pill">
                        <div class="stat-icon purple">&#9993;</div>
                        <div class="stat-text">
                            <div class="label">Total Sent</div>
                            <div class="value">16,478</div>
                            <div class="delta">+15% from last month</div>
                        </div>
                    </div>
                </div>

                <div class="card-header" style="margin-bottom:6px;">
                    <h2 style="font-size:13px;color:var(--text-mid);font-weight:600;">Performance overview</h2>
                </div>
                <div class="legend">
                    <div class="legend-item"><span class="legend-dot" style="background:var(--red);"></span>Sent</div>
                    <div class="legend-item"><span class="legend-dot" style="background:var(--purple);"></span>Click</div>
                    <div class="legend-item"><span class="legend-dot" style="background:var(--green);"></span>Conversion</div>
                </div>

                <div class="chart-container">
                    <div class="chart-yaxis">
                        <span>5k</span>
                        <span>3k</span>
                        <span>2k</span>
                        <span>1k</span>
                        <span>500</span>
                        <span>0</span>
                    </div>
                    <div class="chart-wrap">
                        <svg viewBox="0 0 560 160" preserveAspectRatio="xMidYMid meet">
                            <line x1="0" y1="6" x2="560" y2="6" stroke="#eef0f3" stroke-width="1"/>
                            <line x1="0" y1="38" x2="560" y2="38" stroke="#eef0f3" stroke-width="1"/>
                            <line x1="0" y1="70" x2="560" y2="70" stroke="#eef0f3" stroke-width="1"/>
                            <line x1="0" y1="102" x2="560" y2="102" stroke="#eef0f3" stroke-width="1"/>
                            <line x1="0" y1="134" x2="560" y2="134" stroke="#eef0f3" stroke-width="1"/>

                            <polyline fill="none" stroke="#ef4444" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"
                                points="0,80 80,62 160,90 240,52 320,66 400,34 480,48 560,16" />
                            <polyline fill="none" stroke="#7c3aed" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"
                                points="0,134 80,124 160,138 240,120 320,132 400,106 480,130 560,116" />
                            <polyline fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"
                                points="0,142 80,140 160,144 240,133 320,139 400,129 480,133 560,124" />
                        </svg>
                    </div>
                </div>
                <div class="chart-xaxis">
                    <span>05-7</span>
                    <span>05-8</span>
                    <span>05-12</span>
                    <span>05-16</span>
                    <span>05-20</span>
                    <span>05-25</span>
                    <span>05-28</span>
                </div>

                <div class="stat-row" style="margin-bottom:18px;">
                    <div class="stat-pill">
                        <div class="stat-icon green">&#128200;</div>
                        <div class="stat-text">
                            <div class="label">Total Conversation</div>
                            <div class="value">1,355</div>
                            <div class="delta">+18% from last month</div>
                        </div>
                    </div>
                    <div class="stat-pill">
                        <div class="stat-icon blue">&#128202;</div>
                        <div class="stat-text">
                            <div class="label">Total Campaigns</div>
                            <div class="value">12</div>
                            <div class="delta">+20% from last month</div>
                        </div>
                    </div>
                </div>

                <!-- Top performing Campaigns — still hardcoded; wire this up to
                     real ROI figures once you're tracking spend/revenue per campaign -->
                <div class="roi-card">
                    <div class="roi-header-row">
                        <span>Campaign</span>
                        <span>ROI</span>
                    </div>

                    <div class="roi-row">
                        <span class="campaign-name">Summer Sale Blast</span>
                        <div class="roi-bar-wrap">
                            <div class="roi-bar-track"><div class="roi-bar-fill" style="width:92%;"></div></div>
                            <span class="roi-value">&#8369; 49,359.00</span>
                        </div>
                    </div>

                    <div class="roi-row">
                        <span class="campaign-name">Weekend Flash Sale</span>
                        <div class="roi-bar-wrap">
                            <div class="roi-bar-track"><div class="roi-bar-fill" style="width:80%;"></div></div>
                            <span class="roi-value">&#8369; 40,289.00</span>
                        </div>
                    </div>

                    <div class="roi-row">
                        <span class="campaign-name">Follow us on Instagram!</span>
                        <div class="roi-bar-wrap">
                            <div class="roi-bar-track"><div class="roi-bar-fill" style="width:70%;"></div></div>
                            <span class="roi-value">&#8369; 36,756.00</span>
                        </div>
                    </div>

                    <div class="roi-row">
                        <span class="campaign-name">New Product SMS</span>
                        <div class="roi-bar-wrap">
                            <div class="roi-bar-track"><div class="roi-bar-fill" style="width:63%;"></div></div>
                            <span class="roi-value">&#8369; 34,156.00</span>
                        </div>
                    </div>

                    <div class="roi-row">
                        <span class="campaign-name">Loyalty Reward</span>
                        <div class="roi-bar-wrap">
                            <div class="roi-bar-track"><div class="roi-bar-fill" style="width:55%;"></div></div>
                            <span class="roi-value">&#8369; 29,909.00</span>
                        </div>
                    </div>

                    <footer class="arrow">&#8594;</footer>
                </div>
            </div>

            <!-- Lead Automation -->
            <div class="card">
                <div class="card-header">
                    <h2>Lead Automation</h2>
                    <a href="{{ route('workflow.create') }}" class="btn-primary">+ New Workflow</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Workflow</th>
                            <th>Trigger</th>
                            <th>Status</th>
                            <th>Leads Enrolled</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($workflows as $workflow)
                            <tr>
                                <td>{{ $workflow->name }}</td>
                                <td>{{ $workflow->trigger }}</td>
                                <td><span class="badge {{ $workflow->status }}">{{ ucfirst($workflow->status) }}</span></td>
                                <td>{{ $workflow->leads_enrolled }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">No workflows yet. Click "+ New Workflow" to create one.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="arrow">&#8594;</div>
            </div>

        </div><!-- /col-right -->

    </div><!-- /page-columns -->

</div><!-- /mcm-page -->

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Deep-link support: /mcm?highlight=3 scrolls to and briefly highlights
    // that campaign's row, so notification links can point at a specific
    // record instead of just the general page.
    const params = new URLSearchParams(window.location.search);
    const highlightId = params.get('highlight');
    if (highlightId) {
        const row = document.getElementById('campaign-row-' + highlightId);
        if (row) {
            row.scrollIntoView({ behavior: 'smooth', block: 'center' });
            row.style.transition = 'background-color 0.3s ease';
            row.style.backgroundColor = '#FEF3C7';
            setTimeout(() => { row.style.backgroundColor = ''; }, 2000);
        }
    }
});
</script>
@endsection
