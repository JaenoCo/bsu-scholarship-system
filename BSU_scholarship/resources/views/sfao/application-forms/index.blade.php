<div class="w-full" x-cloak>

    <div x-show="tab === 'all-app-forms'"
         x-transition>
        @include('sfao.application-forms.list')
    </div>

    <div x-show="tab === 'up-app-form'"
         x-transition>
        @include('sfao.application-forms.upload')
    </div>

</div>