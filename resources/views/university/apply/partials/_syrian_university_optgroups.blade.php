<option value="">-- اختر الجامعة / المؤسسة --</option>
<optgroup label="الجامعات الحكومية السورية">
    @foreach($publicUnis as $uni)
        <option value="{{ $uni->id }}" {{ (!$isOther && $selectedId == $uni->id) ? 'selected' : '' }}>{{ $uni->name }}</option>
    @endforeach
</optgroup>
<optgroup label="الجامعات الخاصة السورية">
    @foreach($privateUnis as $uni)
        <option value="{{ $uni->id }}" {{ (!$isOther && $selectedId == $uni->id) ? 'selected' : '' }}>{{ $uni->name }}</option>
    @endforeach
</optgroup>
@if(!empty($institutes) && $institutes->isNotEmpty())
<optgroup label="المعاهد العليا الحكومية السورية">
    @foreach($institutes as $inst)
        <option value="{{ $inst->id }}" {{ (!$isOther && $selectedId == $inst->id) ? 'selected' : '' }}>{{ $inst->name }}</option>
    @endforeach
</optgroup>
@endif
@if(!empty($fallbackUni) && !$publicUnis->contains('id', $fallbackUni->id) && !$privateUnis->contains('id', $fallbackUni->id) && (empty($institutes) || !$institutes->contains('id', $fallbackUni->id)))
    <option value="{{ $fallbackUni->id }}" {{ (!$isOther && $selectedId == $fallbackUni->id) ? 'selected' : '' }}>{{ $fallbackUni->name }}</option>
@endif
<option value="other" {{ $isOther ? 'selected' : '' }}>أخرى (كتابة اسم الجامعة / المعهد يدوياً)</option>
