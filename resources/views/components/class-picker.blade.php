@props(['classes', 'sections', 'subjects' => null, 'sectionRequired' => true])
<div class="contents" x-data="{ classId: @js((string) request('class_id')), sections: @js($sections), subjects: @js($subjects ?? []) }">
    <div>
        <label class="field-label" for="class_id">Class</label>
        <select id="class_id" name="class_id" class="input" x-model="classId" required>
            <option value="">Select class</option>
            @foreach ($classes as $option)
                <option value="{{ $option['value'] }}" @selected((string) request('class_id') === (string) $option['value'])>{{ $option['label'] }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="field-label" for="section_id">Section</label>
        <select id="section_id" name="section_id" class="input" @required($sectionRequired)>
            <option value="">{{ $sectionRequired ? 'Select section' : 'All sections' }}</option>
            <template x-for="o in sections.filter(s => String(s.parent) === classId)" :key="o.value">
                <option :value="o.value" x-text="o.label" :selected="String(o.value) === @js((string) request('section_id'))"></option>
            </template>
        </select>
    </div>
    @if ($subjects !== null)
        <div>
            <label class="field-label" for="subject_id">Subject</label>
            <select id="subject_id" name="subject_id" class="input" required>
                <option value="">Select subject</option>
                <template x-for="o in subjects.filter(s => String(s.parent) === classId)" :key="o.value">
                    <option :value="o.value" x-text="o.label" :selected="String(o.value) === @js((string) request('subject_id'))"></option>
                </template>
            </select>
        </div>
    @endif
</div>
