<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add New Campaign</title>
<link rel="stylesheet" href="{{ asset('css/add-campaign.css') }}">
</head>
<body>

<form class="modal-card" method="POST" action="{{ route('campaigns.store') }}" enctype="multipart/form-data" id="campaignForm">
@csrf

  <div class="modal-header">
    <h1>Add New Campaign</h1>
  </div>

  <div class="modal-body">

    <!-- Stepper -->
    <div class="stepper">
      <div class="step active" data-step="1">
        <div class="step-line-wrap">
          <div class="step-circle">1</div>
          <div class="step-line"></div>
        </div>
        <div class="step-label">Campaign Info</div>
      </div>
      <div class="step" data-step="2">
        <div class="step-line-wrap">
          <div class="step-circle">2</div>
          <div class="step-line"></div>
        </div>
        <div class="step-label">Channel &amp; Content</div>
      </div>
      <div class="step" data-step="3">
        <div class="step-line-wrap">
          <div class="step-circle">3</div>
          <div class="step-line"></div>
        </div>
        <div class="step-label">Schedule</div>
      </div>
      <div class="step" data-step="4">
        <div class="step-line-wrap">
          <div class="step-circle">4</div>
        </div>
        <div class="step-label">Review</div>
      </div>
    </div>

    <div class="content-col">

    @if ($errors->any())
      <div class="form-errors">
        <ul>
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <!-- Form panels -->
    <div class="form-panel">

      <!-- Step 1 -->
      <div class="step-content active" id="step-1">
        <h2>Campaign Information</h2>

        <label class="field-label">Campaign Name <span class="required">*</span></label>
        <input type="text" class="field-input" name="name" value="{{ old('name') }}" placeholder="Enter Campaign Name">

        <label class="field-label">Campaign Type <span class="required">*</span></label>
        <select class="field-input select-input" name="type">
          <option value="" disabled {{ old('type') ? '' : 'selected' }}>Select Campaign Type</option>
          @foreach (['Promotional', 'Product Launch', 'Engagement', 'Retention'] as $option)
            <option value="{{ $option }}" @selected(old('type') === $option)>{{ $option }}</option>
          @endforeach
        </select>

        <label class="field-label">Objective <span class="required">*</span></label>
        <select class="field-input select-input" name="objective">
          <option value="" disabled {{ old('objective') ? '' : 'selected' }}>Select Objective</option>
          @foreach (['Brand Awareness', 'Lead Generation', 'Sales Conversion', 'Customer Retention'] as $option)
            <option value="{{ $option }}" @selected(old('objective') === $option)>{{ $option }}</option>
          @endforeach
        </select>

        <label class="field-label">Description</label>
        <textarea class="field-input textarea-input" name="description" placeholder="Enter campaign description (optional)">{{ old('description') }}</textarea>

        <label class="field-label">Status <span class="required">*</span></label>
        <input type="hidden" name="status" id="statusInput" value="{{ old('status', 'scheduled') }}">
        <div class="status-toggle-wrap">
          <button type="button" class="status-toggle {{ old('status', 'scheduled') === 'scheduled' ? 'active' : '' }}" data-status="scheduled" id="scheduledBtn">Scheduled</button>
          <button type="button" class="status-toggle {{ old('status') === 'draft' ? 'active' : '' }}" data-status="draft" id="draftBtn">Draft</button>
        </div>
      </div>

      <!-- Step 2 -->
      <div class="step-content" id="step-2">
        <h2>Channel &amp; Content</h2>
        <p class="step-subtitle">Choose your channel and create your campaign message.</p>

        <div class="field-row">
          <div class="field-col">
            <label class="field-label">Select Channel <span class="required">*</span></label>
            <select class="field-input select-input" name="channel">
              <option value="" disabled {{ old('channel') ? '' : 'selected' }}>Select Channel</option>
              @foreach (['Email', 'SMS', 'Instagram', 'TikTok'] as $option)
                <option value="{{ $option }}" @selected(old('channel') === $option)>{{ $option }}</option>
              @endforeach
            </select>
          </div>
          <div class="field-col">
            <label class="field-label">Audience Segment <span class="required">*</span></label>
            <select class="field-input select-input" name="audience">
              <option value="" disabled {{ old('audience') ? '' : 'selected' }}>Select Audience</option>
              @foreach (['All Customers', 'New Leads', 'Qualified Leads', 'Past Customers', 'Existing Customers'] as $option)
                <option value="{{ $option }}" @selected(old('audience') === $option)>{{ $option }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <label class="field-label">Subject Line <span class="required">*</span></label>
        <input type="text" class="field-input" id="subjectLine" name="subject_line" value="{{ old('subject_line') }}" placeholder="Enter subject line">

        <label class="field-label">Campaign Message <span class="required">*</span></label>
        <textarea class="field-input textarea-input message-input" id="campaignMessage" name="message" maxlength="1000" placeholder="Write your campaign message">{{ old('message') }}</textarea>
        <div class="char-counter"><span id="charCount">0</span>/1000</div>

        <label class="field-label">Attach Media (optional)</label>
        <div class="upload-row">
          <div class="upload-dropzone" id="dropzoneBtn">
            <span class="upload-icon">&#8593;</span>
            <span class="upload-text">Upload Image / Video</span>
          </div>
          <button type="button" class="btn-browse" id="browseBtn">
            <span class="browse-icon">&#128193;</span> Browse Files
          </button>
          <input type="file" id="mediaInput" name="media" hidden>
        </div>
        <div class="upload-hint">Supported: JPG, PNG, MP4 (Max size: 10MB)</div>
      </div>

      <!-- Step 3 -->
      <div class="step-content" id="step-3">
        <h2>Schedule Campaign</h2>
        <p class="step-subtitle">Set when your campaign will be sent.</p>

        <div class="field-row">
          <div class="field-col">
            <label class="field-label">Start Date <span class="required">*</span></label>
            <input type="date" class="field-input" name="send_date" value="{{ old('send_date') }}">
          </div>
          <div class="field-col">
            <label class="field-label">Start Time <span class="required">*</span></label>
            <input type="time" class="field-input" name="send_time" value="{{ old('send_time') }}">
          </div>
        </div>

        <div class="field-row">
          <div class="field-col">
            <label class="field-label">End Date (Optional)</label>
            <input type="date" class="field-input" name="end_date" value="{{ old('end_date') }}">
          </div>
          <div class="field-col">
            <label class="field-label">Repeat Time</label>
            <input type="time" class="field-input" name="repeat_time" id="repeatTime" value="{{ old('repeat_time') }}" disabled>
          </div>
        </div>

        <label class="field-label">Repeat Campaign</label>
        <select class="field-input select-input" name="repeat_campaign" id="repeatSelect">
          @foreach (['No Repeat', 'Daily', 'Weekly', 'Monthly'] as $option)
            <option value="{{ $option }}" @selected(old('repeat_campaign', 'No Repeat') === $option)>{{ $option }}</option>
          @endforeach
        </select>

        <label class="field-label">Time Zone</label>
        <select class="field-input select-input" name="timezone">
          <option value="" disabled {{ old('timezone') ? '' : 'selected' }}>Select Time Zone</option>
          @foreach (['(GMT+08:00) Philippines', '(GMT+00:00) UTC', '(GMT-05:00) New York'] as $option)
            <option value="{{ $option }}" @selected(old('timezone') === $option)>{{ $option }}</option>
          @endforeach
        </select>

        <label class="field-label">Send Option</label>
        <div class="send-option-row">
          <label class="radio-option">
            <input type="radio" name="send_option" value="immediate" {{ old('send_option') === 'immediate' ? 'checked' : '' }}>
            Send Immediately
          </label>
          <label class="radio-option">
            <input type="radio" name="send_option" value="schedule" {{ old('send_option', 'schedule') === 'schedule' ? 'checked' : '' }}>
            Schedule for Later
          </label>
        </div>

        <label class="field-label">Notes (Optional)</label>
        <textarea class="field-input textarea-input" name="notes" placeholder="Additional scheduling notes...">{{ old('notes') }}</textarea>
      </div>

      <!-- Step 4 -->
      <div class="step-content" id="step-4">
        <h2>Review Campaign</h2>
        <p class="review-note">Please review the details of your campaign before creating it.</p>

        <div class="review-layout">
          <div class="review-fields" id="reviewFields"></div>

          <div class="message-preview">
            <div class="message-preview-label">Message Preview</div>
            <div class="message-preview-box" id="messagePreviewBox"></div>
          </div>
        </div>

        <label class="confirm-row">
          <input type="checkbox" id="confirmReview">
          I have reviewed the campaign details and confirm they are correct.
        </label>
      </div>

    </div>
    </div>
  </div>

  <div class="modal-footer">
    <button type="button" class="btn-cancel" id="cancelBtn">Cancel</button>
    <button type="button" class="btn-back" id="backBtn" style="display:none;">Back</button>
    <button type="submit" class="btn-next" id="nextBtn">Next</button>
  </div>

</form>

<script>
  const form = document.getElementById('campaignForm');
  const steps = document.querySelectorAll('.step');
  const contents = document.querySelectorAll('.step-content');
  const nextBtn = document.getElementById('nextBtn');
  const backBtn = document.getElementById('backBtn');
  const cancelBtn = document.getElementById('cancelBtn');
  const confirmCheckbox = document.getElementById('confirmReview');
  let current = 1;
  const total = steps.length;

  confirmCheckbox.addEventListener('change', () => {
    if (current === total) nextBtn.disabled = !confirmCheckbox.checked;
  });

  function render() {
    steps.forEach(s => {
      const n = parseInt(s.dataset.step);
      s.classList.remove('active');
      if (n === current) s.classList.add('active');
    });
    contents.forEach(c => c.classList.remove('active'));
    document.getElementById('step-' + current).classList.add('active');

    nextBtn.textContent = current === total ? 'Create Campaign' : 'Next';
    backBtn.style.display = current === 1 ? 'none' : 'inline-block';

    if (current === total) {
      buildReview();
      nextBtn.disabled = !confirmCheckbox.checked;
    } else {
      nextBtn.disabled = false;
    }
  }

  const fieldIcons = {
    name: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 9h6M7 13h4"/></svg>',
    type: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="8" cy="12" r="5.2"/><path d="M13.5 8.5 20 6l-2.5 6.5L20 19l-6.5-2.5"/></svg>',
    objective: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="8.5"/><circle cx="12" cy="12" r="4.5"/><circle cx="12" cy="12" r="0.8" fill="currentColor"/></svg>',
    channel: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 6.5 8 6 8-6"/></svg>',
    audience: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="9" r="3"/><path d="M3.5 19c0-3 2.5-5.5 5.5-5.5s5.5 2.5 5.5 5.5"/><circle cx="17" cy="10" r="2.3"/><path d="M15.5 19c0-2.3 1.4-4.1 3.2-4.8"/></svg>',
    subject: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16v11a2 2 0 0 1-2 2H9l-5 3z"/><path d="M8 9h8M8 12.5h5"/></svg>',
    schedule: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3.5" y="5" width="17" height="15" rx="2"/><path d="M3.5 9.5h17M8 3v4M16 3v4"/></svg>',
  };

  function formatDateDisplay(dateStr) {
    if (!dateStr) return null;
    const [y, m, d] = dateStr.split('-').map(Number);
    return new Date(y, m - 1, d).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
  }

  function formatTimeDisplay(timeStr) {
    if (!timeStr) return null;
    let [h, m] = timeStr.split(':').map(Number);
    const suffix = h >= 12 ? 'PM' : 'AM';
    h = h % 12 || 12;
    return `${h}:${String(m).padStart(2, '0')} ${suffix}`;
  }

  function buildReview() {
    const dateDisplay = formatDateDisplay(form.send_date.value) || '—';
    const timeDisplay = formatTimeDisplay(form.send_time.value);
    const scheduleLine1 = timeDisplay ? `${dateDisplay} at ${timeDisplay}` : dateDisplay;
    const scheduleLine2 = form.repeat_campaign.value || 'No Repeat';

    const rows = [
      ['name', 'Campaign Name', form.name.value || '—'],
      ['type', 'Campaign Type', form.type.value || '—'],
      ['objective', 'Objective', form.objective.value || '—'],
      ['channel', 'Channel', form.channel.value || '—'],
      ['audience', 'Audience', form.audience.value || '—'],
      ['subject', 'Subject', form.subject_line.value || '—'],
      ['schedule', 'Schedule', `${scheduleLine1}<br><span class="review-field-sub">${scheduleLine2}</span>`],
    ];

    document.getElementById('reviewFields').innerHTML = rows.map(
      ([key, label, value]) => `
        <div class="review-field">
          <span class="review-field-icon">${fieldIcons[key]}</span>
          <span class="review-field-label">${label}</span>
          <span class="review-field-value">${value}</span>
        </div>`
    ).join('');

    const messageHtml = (form.message.value || 'Your campaign message will appear here.')
      .replace(/&/g, '&amp;').replace(/</g, '&lt;')
      .split('\n').join('<br>');
    document.getElementById('messagePreviewBox').innerHTML =
      `<strong>${form.subject_line.value || 'Subject line'}</strong><br><br>${messageHtml}`;
  }

  // Next/Submit is a single <button type="submit">. Before the final step
  // we stop the real submit and just advance the wizard; on the review
  // step we let the click through so the form actually POSTs to the server.
  nextBtn.addEventListener('click', (e) => {
    if (current < total) {
      e.preventDefault();
      current++;
      render();
    }
    // else: no preventDefault — real form submission to campaigns.store
  });

  backBtn.addEventListener('click', () => {
    if (current > 1) {
      current--;
      render();
    }
  });

  cancelBtn.addEventListener('click', () => {
    window.location.href = "{{ route('mcm.index') }}";
  });

  steps.forEach(s => {
    s.addEventListener('click', () => {
      current = parseInt(s.dataset.step);
      render();
    });
  });

  // Status toggle (Scheduled / Draft)
  const scheduledBtn = document.getElementById('scheduledBtn');
  const draftBtn = document.getElementById('draftBtn');
  const statusInput = document.getElementById('statusInput');
  [scheduledBtn, draftBtn].forEach(btn => {
    btn.addEventListener('click', () => {
      scheduledBtn.classList.remove('active');
      draftBtn.classList.remove('active');
      btn.classList.add('active');
      statusInput.value = btn.dataset.status;
    });
  });

  // Repeat Time only makes sense once a repeat frequency is chosen
  const repeatSelect = document.getElementById('repeatSelect');
  const repeatTime = document.getElementById('repeatTime');
  function syncRepeatTime() {
    repeatTime.disabled = repeatSelect.value === 'No Repeat';
    if (repeatTime.disabled) repeatTime.value = '';
  }
  repeatSelect.addEventListener('change', syncRepeatTime);
  syncRepeatTime();

  // Character counter for campaign message
  const campaignMessage = document.getElementById('campaignMessage');
  const charCount = document.getElementById('charCount');
  charCount.textContent = campaignMessage.value.length;
  campaignMessage.addEventListener('input', () => {
    charCount.textContent = campaignMessage.value.length;
  });

  // Browse Files (and the dropzone box itself) both trigger the hidden file input
  const browseBtn = document.getElementById('browseBtn');
  const dropzoneBtn = document.getElementById('dropzoneBtn');
  const mediaInput = document.getElementById('mediaInput');
  browseBtn.addEventListener('click', () => mediaInput.click());
  dropzoneBtn.addEventListener('click', () => mediaInput.click());
  mediaInput.addEventListener('change', () => {
    if (mediaInput.files.length) {
      dropzoneBtn.querySelector('.upload-text').textContent = mediaInput.files[0].name;
    }
  });

  // If the page reloaded with validation errors, jump back to the first
  // step that's missing a value so the user isn't left staring
  // at the review screen with no idea what's wrong.
  @if ($errors->any())
    (function () {
      const stepOfField = {
        name: 1, type: 1, objective: 1,
        channel: 2, audience: 2, subject_line: 2, message: 2,
        send_date: 3,
      };
      const firstError = @json(array_keys($errors->toArray())[0] ?? null);
      current = stepOfField[firstError] || 1;
      render();
    })();
  @endif
</script>

</body>
</html>
