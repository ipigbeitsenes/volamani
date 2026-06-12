{{--
    Reusable follow/unfollow control for a vendor store.
    Params:
      $vendor  (required) — the Vendor to follow
      $size    (optional) — Bootstrap button size suffix: 'sm' (default) or ''
      $block   (optional) — bool, render full-width (default false)
--}}
@php($vlmSize = ($size ?? 'sm'))
@php($vlmBlock = ($block ?? false))
@auth
    @if(auth()->id() !== $vendor->user_id)
        @php($vlmFollowing = auth()->user()->isFollowing($vendor))
        <form method="POST" action="{{ route('follow.toggle', $vendor) }}" class="m-0 {{ $vlmBlock ? 'd-grid' : '' }}">
            @csrf
            <button type="submit"
                    class="btn {{ $vlmSize ? 'btn-' . $vlmSize : '' }} fw-semibold {{ $vlmFollowing ? 'btn-light border' : 'btn-primary' }}">
                <i class="bi {{ $vlmFollowing ? 'bi-person-check-fill' : 'bi-person-plus' }} me-1"></i>{{ $vlmFollowing ? 'Following' : 'Follow' }}
            </button>
        </form>
    @endif
@else
    <a href="{{ route('login') }}"
       class="btn {{ $vlmSize ? 'btn-' . $vlmSize : '' }} btn-primary fw-semibold {{ $vlmBlock ? 'w-100' : '' }}">
        <i class="bi bi-person-plus me-1"></i>Follow
    </a>
@endauth
