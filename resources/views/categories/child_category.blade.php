
<option value="{{ $child_category->id }}">{{ $value." ".$child_category->getTranslation('name') }}</option>
@if ($child_category->categories)
@php
   // for ($i=0; $i < $child_category->level; $i++){
        $value .= $child_category->getTranslation('name').'/';
  //  }
@endphp
    @foreach ($child_category->categories as $childCategory)
        @include('categories.child_category', ['child_category' => $childCategory,'value'=>$value])
    @endforeach
@endif
