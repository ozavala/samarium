<div class="card shadow-sm">


  <div class="card-body p-3">
    <h1 class="h5 o-heading mb-4">
      Create link
    </h1>
    <div class="form-group">
      <label>Url *</label>
      <input type="text"
          class="form-control"
          wire:model="url">
      @error ('url') <span class="text-danger">{{ $message }}</span> @enderror
    </div>
    <div class="form-group">
      <label>Description</label>
      <input type="text"
          class="form-control"
          wire:model="description">
      @error ('description') <span class="text-danger">{{ $message }}</span> @enderror
    </div>
    <div class="py-3 m-0">
      @include ('partials.button-store')
      @include ('partials.button-cancel', ['clickEmitEventName' => 'urlLinkCreateCancelled',])
      @include ('partials.dashboard.spinner-button')
    </div>
  </div>


</div>
