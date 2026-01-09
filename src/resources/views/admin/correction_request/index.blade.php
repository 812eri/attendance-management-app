@extends('layouts.admin')

@section('title', '申請一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endsection

@section('content')
<div class="container">
    <h1 class="page-title">申請一覧</h1>

    <div class="tab-menu">
        <button class="tab-btn active" onclick="openTab(event, 'pending')">承認待ち</button>
        <button class="tab-btn" onclick="openTab(event, 'approved')">承認済み</button>
    </div>

    <div id="pending" class="tab-content active">
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendingRequests as $request)
                <tr>
                    <td>承認待ち</td>
                    <td>{{ $request->user->name }}</td>
                    <td>@if($request->attendance)
                            {{ \Carbon\Carbon::parse($request->attendance->date)->format('Y/m/d') }}
                        @else
                            <span style="color:red;">データなし</span>
                        @endif
                    </td>
                    <td>{{ $request->new_remarks }}</td>
                    <td>{{ $request->created_at ? $request->created_at->format('Y/m/d') : '-' }}</td>
                    <td>
                        <a href="{{ route('admin.stamp_correction_request.approve', $request->id) }}" class="btn-detail">詳細</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div id="approved" class="tab-content">
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach($approvedRequests as $request)
                <tr>
                    <td>承認済み</td>
                    <td>{{ $request->user->name }}</td>
                    <td>@if($request->attendance)
                            {{ \Carbon\Carbon::parse($request->attendance->date)->format('Y/m/d') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $request->new_remarks }}</td>
                    <td>{{ $request->created_at ? $request->created_at->format('Y/m/d') : '-' }}</td>
                    <td>
                        <a href="{{ route('admin.stamp_correction_request.approve', $request->id) }}" class="btn-detail">詳細</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
    function openTab(evt, tabName) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tab-content");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }

    tablinks = document.getElementsByClassName("tab-btn");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }

    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.className += " active";
}
</script>
@endsection