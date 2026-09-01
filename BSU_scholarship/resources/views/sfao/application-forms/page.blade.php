@extends('layouts.dashboard', ['user' => $user, 'title' => 'Application Forms'])

@section('navbar')
    <x-layout.navbar
        title="Application Forms"
        :user="$user"
        :settings="false"
        :logout="true"
    />
@endsection

@section('sidebar-menu')
    @include('sfao.components.sidebar-menu', ['user' => $user, 'sfaoCampus' => $sfaoCampus])
@endsection

@section('content')
    <div x-data="{
        tab: @js($activeTab),
        navigateToDashboard(tab) {
            if (['all-app-forms', 'up-app-form'].includes(tab)) {
                this.tab = tab;
                return;
            }

            window.location.href = @js(route('sfao.dashboard')) + '?tabs=' + encodeURIComponent(tab);
        }
    }" x-on:switch-tab.window="navigateToDashboard($event.detail)">
        @include('sfao.application-forms.index')
    </div>
@endsection
