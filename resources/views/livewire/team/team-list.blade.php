<div>

  <!-- Flash message div -->
  @if (session()->has('message'))
    @include ('partials.flash-message', [
        'flashMessage' => session('message'),
    ])
  @endif

  <button wire:loading class="btn btn-danger-rm" style="font-size: 1.5rem;">
    <div class="spinner-border text-info mr-3" role="status">
      <span class="sr-only">Loading...</span>
    </div>
  </button>

  {{-- Top flash cards --}}
  @if (true)
  <div class="row mb-1">
    <div class="col-md-12">
      <div class="mb-4">
        @include ('partials.misc.glance-card', [
            'bsBgClass' => 'bg-white',
            'btnRoute' => '',
            'iconFaClass' => 'fas fa-users',
            'btnTextPrimary' => 'Teams',
            'btnTextSecondary' => $teamsCount,
        ])
      </div>
    </div>
  </div>
  @endif

  {{-- Search bar --}}
  <div class="mb-3 p-2 bg-white">
    <input type="text" wire:model.defer="team_search_name" />
    <button class="btn btn-primary" wire:click="search">
      Search
    </button>
  </div>

  @if ($searchResultTeams != null && count($searchResultTeams))
    {{-- Show in bigger screens --}}
    <div class="table-responsive d-none d-md-block">
      <table class="table table-sm-rm table-bordered-rm table-hover shadow-sm border">
        <thead>
          <tr class="{{ env('OC_ASCENT_BG_COLOR', 'bg-success') }}
              {{ env('OC_ASCENT_TEXT_COLOR', 'text-white') }}
              p-4" style="font-size: 1rem;">
            <th>
              Team
            </th>
            <th>
              Action
            </th>
          </tr>
        </thead>

        <tbody class="bg-white">
          @foreach ($searchResultTeams as $team)
            @if (false)
            @if ($team->team_type != 'playing_team')
              @continue
            @endif
            @endif
            <tr>
              <td>
                @if ($team->image_path)
                  <img src="{{ asset('storage/' . $team->image_path) }}" class="img-fluid" style="height: 50px;">
                @endif
                <span wire:click="$emit('displayTeam', {{ $team }})" role="button">
                  {{ $team->name }}
                </span>
              </td>
              <td>
                <button class="btn mr-3" wire:click="">
                  <i class="fas fa-trash-alt"></i>
                </button>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>

    </div>
  @endif

  @if ($teams != null && count($teams) > 0)
    @if (true)
    {{-- Show in bigger screens --}}
    <div class="table-responsive d-none d-md-block">
      <table class="table table-sm-rm table-bordered-rm table-hover shadow-sm border">
        <thead>
          <tr class="{{ env('OC_ASCENT_BG_COLOR', 'bg-success') }}
              {{ env('OC_ASCENT_TEXT_COLOR', 'text-white') }}
              p-4" style="font-size: 1rem;">
            <th>
              Team
            </th>
            <th>
              Action
            </th>
          </tr>
        </thead>

        <tbody class="bg-white">
          @foreach ($teams as $team)
            @if (false)
            @if ($team->team_type != 'playing_team')
              @continue
            @endif
            @endif
            <tr>
              <td>
                @if ($team->image_path)
                  <img src="{{ asset('storage/' . $team->image_path) }}" class="img-fluid" style="height: 50px;">
                @endif
                <span wire:click="$emit('displayTeam', {{ $team }})" role="button">
                  {{ $team->name }}
                </span>
              </td>
              <td>
                <button class="btn mr-3" wire:click="deleteTeam({{ $team }})">
                  <i class="fas fa-trash-alt"></i>
                </button>
                @if ($modes['delete'])
                  @if ($deletingTeam->team_id == $team->team_id)

                    @if ($modes['cannotDelete'])
                      <span class="text-danger mr-3">
                        Cannot be deleted
                      </span>
                      <span class="btn btn-light mr-3" wire:click="deleteTeamCancel">
                        Cancel
                      </span>
                    @else
                      <span class="btn btn-danger mr-3" wire:click="confirmDeleteTeam">
                        Confirm delete
                      </span>
                      <span class="btn btn-light mr-3" wire:click="deleteTeamCancel">
                        Cancel
                      </span>
                    @endif

                  @endif
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>

    </div>
    @endif
    {{-- Pagination links --}}
    {{ $teams->links() }}
  @else
    <div class="p-2 text-secondary">
      <i class="fas fa-exclamation-circle mr-2"></i>
      No teams
    </div>
  @endif
</div>
