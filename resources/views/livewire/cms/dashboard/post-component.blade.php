<div>

  @if (true || $modes['listPostMode'] || !array_search(true, $modes))
  {{-- Bigger screen menu --}}
  <x-toolbar-classic toolbarTitle="Posts" titleNone="yes">

    @include ('partials.dashboard.spinner-button')

    @if ($modes['listPostMode'] || !array_search(true, $modes))
    @include ('partials.dashboard.tool-bar-button-pill', [
        'btnClickMethod' => "enterMode('createPostMode')",
        'btnIconFaClass' => 'fas fa-plus-circle',
        'btnText' => 'Create',
        'btnCheckMode' => 'createPostMode',
    ])
    @endif

    @if (false)
    @include ('partials.dashboard.tool-bar-button-pill', [
        'btnClickMethod' => "enterMode('listPostMode')",
        'btnIconFaClass' => 'fas fa-list',
        'btnText' => 'List',
        'btnCheckMode' => 'listPostMode',
    ])

    @include ('partials.dashboard.tool-bar-button-pill', [
        'btnClickMethod' => "enterMode('createPostCategoryMode')",
        'btnIconFaClass' => 'fas fa-plus-circle',
        'btnText' => 'Create post category',
        'btnCheckMode' => 'createPostCategoryMode',
    ])

    @if ($modes['displayPostMode'])
      @include ('partials.dashboard.tool-bar-button-pill', [
          'btnClickMethod' => "",
          'btnIconFaClass' => 'fas fa-circle',
          'btnText' => 'Post display',
          'btnCheckMode' => 'displayPostMode',
      ])
    @endif

    @include ('partials.dashboard.tool-bar-button-pill', [
        'btnClickMethod' => "clearModes",
        'btnIconFaClass' => 'fas fa-times',
        'btnText' => '',
        'btnCheckMode' => '',
    ])
    @endif

  </x-toolbar-classic>
  @endif

  <!-- Flash message div -->
  @if (session()->has('message'))
    <div class="p-2">
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle mr-3"></i>
        {{ session('message') }}
        <button type="button" class="close text-white" data-dismiss="alert" aria-label="Close">
          <span class="text-danger" aria-hidden="true">&times;</span>
        </button>
      </div>
    </div>
  @endif

  @if ($modes['createPostMode'])
    @livewire ('cms.dashboard.webpage-create', ['is_post' => 'yes',])
  @elseif ($modes['listPostMode'])
    @livewire ('cms.dashboard.post-list')
  @elseif ($modes['displayPostMode'])
    @livewire ('cms.dashboard.webpage-display', ['webpage' => $displayingPost,])
  @elseif ($modes['createPostCategoryMode'])
    @livewire ('cms.dashboard.post-category-create')
  @else
    @livewire ('cms.dashboard.post-list')
  @endif
</div>
