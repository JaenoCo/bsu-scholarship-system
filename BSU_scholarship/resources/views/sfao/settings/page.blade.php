@extends('layouts.dashboard', ['user' => $user, 'title' => 'Account Settings'])

@section('navbar')
    <x-layout.navbar
        title="Account Settings"
        :user="$user"
        :settings="false"
        :logout="true"
        :action-text="'Back to Dashboard'"
        :action-url="route('sfao.dashboard', ['tabs' => 'analytics_scholarships'])"
        action-title="Return to the SFAO dashboard"
    />
@endsection

@section('sidebar-menu')
    @include('sfao.components.sidebar-menu', ['user' => $user, 'sfaoCampus' => $sfaoCampus])
@endsection

@section('content')
    <div x-data="{
        tab: @js($activeTab),
        navigateToDashboard(tab) {
            if (['account-info', 'account-security'].includes(tab)) {
                this.tab = tab;
                return;
            }

            window.location.href = @js(route('sfao.dashboard')) + '?tabs=' + encodeURIComponent(tab);
        }
    }" x-on:switch-tab.window="navigateToDashboard($event.detail)">
        @include('sfao.settings.index')
    </div>
@endsection
