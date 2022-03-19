@extends ('website.base')

@section ('fbOgMetaTags')
<meta property="og:url"                content="{{ Request::url() }}" />
<meta property="og:type"               content="article" />
<meta property="og:title"              content="Kimchi Ramen | Authentic korean food in Kathmandu." />
<meta property="og:description"        content=" Order Online Kathmandu. Discount available" />
<meta property="og:image"              content="{{ asset('storage/' . $company->logo_image_path) }}" />
@endsection

@section ('content')
  @livewire ('website-home-component')
@endsection

