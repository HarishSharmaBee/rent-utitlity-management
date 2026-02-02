@extends('layouts/contentNavbarLayout')

@section('content')
{{--  <h5 class="pb-1 mb-6">Horizontal</h5>  --}}
<div class="row mb-12 g-6">
  @if(session('success'))
  <div class="alert alert-success">
  {{ session('success') }}
  </div>
@endif
@if(session('error'))
  <div class="alert alert-error">
  {{ session('error') }}
  </div>
@endif
  <div class="col-md">
    <div class="card">
      <div class="row g-0">
        <div class="col-md-4">
          <img class="card-img card-img-left" src="{{  $movie->getImage() }}" alt="Card image" />
        </div>
        <div class="col-md-8">
          <div class="card-body">
            <div class="text-right">
              
              <a href="{{ route('movie.cast.create', ['movie_id'=>$movie->id])}}" title="Cast" class="btn btn-dark btn-sm me-2"><i class="ri-group-line"></i></a>

              <a href="{{route('movie.crew.create', ['movie_id'=>$movie->id])}}" title="Crew" class="btn btn-info btn-sm me-2"><i class="ri-group-3-fill"></i></a>

              <a href="{{route('movie.document.create', ['movie_id'=>$movie->id])}}" title="Document" class="btn btn-warning btn-sm me-2"><i class="ri-file-list-line"></i></a>

              <a href="{{route('movie.video.create', ['movie_id'=>$movie->id])}}" title="Video" class="btn btn-danger btn-sm me-2"><i class="ri-video-chat-line"></i></a>

              <a href="{{route('movie.budget.create', ['movie_id'=>$movie->id])}}" title="Budget" class="btn btn-secondary btn-sm me-2"><i class="ri-cash-line"></i></a>

              <a href="{{route('movie.finance.create', ['movie_id'=>$movie->id])}}" title="Finance" class="btn btn-success btn-sm me-2"><i class="ri-bank-fill"></i></a>
              <a href="{{route('movies.index')}}" title="Back" class="btn btn-secondary btn-sm me-2">Back</a>

              @if($movie->isMovieComplete())
                @if($movie->is_live)
                  <button type="button" class="btn btn-dark">Published</button>
                @else
                  <form method="POST" class="d-inline" action="{{ route('movies.publish', $movie->id) }}">
                      @csrf
                      <button type="submit" class="btn btn-success">Publish</button>
                  </form>
                @endif
              @else
                <div class="alert alert-warning">
                    Please complete all sections (crew, cast, documents, videos, financials, budget) before publishing.
                </div>
              @endif
            </div>
            <hr/>
            <h5 class="card-title">Movie Name : {{ $movie->name }}</h5>
            <h5 class="card-title">Production Date : {{ $movie->production_date }}</h5>
          </div>
          
        </div>
      </div>
    </div>
    
  </div>
  {{--  <div class="col-md">
    <div class="card">
      <div class="row g-0">
        <div class="col-md-8">
          <div class="card-body">
            <h5 class="card-title">Card title</h5>
            <p class="card-text">
              This is a wider card with supporting text below as a natural lead-in to additional content. This content
              is a
              little bit longer.
            </p>
            <p class="card-text"><small class="text-muted">Last updated 3 mins ago</small></p>
          </div>
        </div>
        <div class="col-md-4">
          <img class="card-img card-img-right" src="{{asset('assets/img/elements/17.jpg')}}" alt="Card image" />
        </div>
      </div>
    </div>
  </div>  --}}
  
</div>
<div class="card mb-6">
  <div class="card-body">
<div class="row">
  
  <div class="col-lg-12">
    <small class="text-light fw-medium">Details</small>
    <div class="demo-inline-spacing mt-4">
      <div class="list-group list-group-horizontal-md text-md-center">
        <a class="list-group-item list-group-item-action active" id="storyline-list-item" data-bs-toggle="list" href="#horizontal-storyline">Storyline</a>
        <a class="list-group-item list-group-item-action" id="synopsis-list-item" data-bs-toggle="list" href="#horizontal-synopsis">Synopsis</a>
        <a class="list-group-item list-group-item-action" id="logline-list-item" data-bs-toggle="list" href="#horizontal-logline">Logline</a>
        <a class="list-group-item list-group-item-action" id="casts-list-item" data-bs-toggle="list" href="#horizontal-casts">Casts</a>
        <a class="list-group-item list-group-item-action" id="crews-list-item" data-bs-toggle="list" href="#horizontal-crews">Crews</a>
        <a class="list-group-item list-group-item-action" id="documents-list-item" data-bs-toggle="list" href="#horizontal-documents">Documents</a>
        <a class="list-group-item list-group-item-action" id="videos-list-item" data-bs-toggle="list" href="#horizontal-videos">Videos</a>
        <a class="list-group-item list-group-item-action" id="budget-list-item" data-bs-toggle="list" href="#horizontal-budget">Budget</a>
        <a class="list-group-item list-group-item-action" id="finance-list-item" data-bs-toggle="list" href="#horizontal-finance">Finance</a>

      </div>
      <div class="tab-content px-0 pt-1 mt-0">
        <div class="tab-pane fade show active" id="horizontal-storyline">
          {{ $movie->storyline }}
        </div>
        <div class="tab-pane fade" id="horizontal-synopsis">
          {{ $movie->synopsis }}
        </div>
        <div class="tab-pane fade" id="horizontal-logline">
          {{ $movie->logline }}
        </div>
        <div class="tab-pane fade" id="horizontal-casts">
          <!-- Data Tables -->
    <div class="col-12">
    <div class="card overflow-hidden">
      <div class="table-responsive">
      <table class="table table-sm">
        <thead>
        <tr>
          <th class="text-truncate">User</th>
          <th class="text-truncate">Role</th>
        </tr>
        </thead>
        <tbody>
          @foreach($movie->movieCasts as $cast)
            <tr>
              <td>
              <div class="d-flex align-items-center">
                <div class="avatar avatar-sm me-4">
                <img src="{{ $cast->getImage() }}" alt="Avatar" class="rounded-circle">
                </div>
                <div>
                <h6 class="mb-0 text-truncate">{{ $cast->name }}</h6>
                {{--  <small class="text-truncate">@amiccoo</small>  --}}
                </div>
              </div>
              </td>
              <td class="text-truncate">
              <div class="d-flex align-items-center">
                <i class="ri-vip-crown-line ri-22px text-primary me-2"></i>
                <span>{{ $cast->role }}</span>
              </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
      </div>
    </div>
    </div>
    <!--/ Data Tables -->
        </div>
        <div class="tab-pane fade" id="horizontal-crews">
          <div class="col-12">
            <div class="card overflow-hidden">
              <div class="table-responsive">
              <table class="table table-sm">
                <thead>
                <tr>
                  <th class="text-truncate">User</th>
                  <th class="text-truncate">Position</th>
                </tr>
                </thead>
                <tbody>
                  @foreach($movie->movieCrews as $crew)
                  <tr>
                    <td>
                    <div class="d-flex align-items-center">
                      <div class="avatar avatar-sm me-4">
                      <img src="{{$crew->getImage()}}" alt="Avatar" class="rounded-circle">
                      </div>
                      <div>
                      <h6 class="mb-0 text-truncate">{{ $crew->name }}</h6>
                      {{--  <small class="text-truncate">@amiccoo</small>  --}}
                      </div>
                    </div>
                    </td>
                    <td class="text-truncate">
                    <div class="d-flex align-items-center">
                      <i class="ri-vip-crown-line ri-22px text-primary me-2"></i>
                      <span>{{ $crew->position }}</span>
                    </div>
                    </td>
                  </tr>
                @endforeach
                </tbody>
              </table>
              </div>
            </div>
          </div>
          <!--/ Data Tables -->
        </div>
        <div class="tab-pane fade" id="horizontal-documents">
          <div class="col-12">
            <div class="card overflow-hidden">
              <div class="table-responsive">
              <table class="table table-sm">
                <thead>
                <tr>
                  <th class="text-truncate">Type</th>
                  <th class="text-truncate">File</th>
                </tr>
                </thead>
                <tbody>
                  @foreach($movie->documents as $document)
                  <tr>
                    <td class="text-truncate">
                    <div class="d-flex align-items-center">
                      <span>{{ $document->type_title }}</span>
                    </div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm me-4">
                          <a href="{{ $document->getFile() }}" target="_blank" download>
                            <i class="ri-file-pdf-2-line text-danger"></i>
                          </a>                      
                        </div>
                      </div>
                      </td>
                  </tr>
                @endforeach
                </tbody>
              </table>
              </div>
            </div>
          </div>
        </div>
        <div class="tab-pane fade" id="horizontal-videos">
          <div class="col-12">
            <div class="card overflow-hidden">
              <div class="table-responsive">
              <table class="table table-sm">
                <thead>
                <tr>
                  <th class="text-truncate">Title</th>
                  <th class="text-truncate">File</th>
                </tr>
                </thead>
                <tbody>
                  @if(!empty($movie->videos))
                  <tr>
                    <td class="text-truncate">
                    <div class="d-flex align-items-center">
                      <span>{{ $movie->videos->title }}</span>
                    </div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center">
                        @php
                          preg_match('/(?:v=|\/)([0-9A-Za-z_-]{11})/', $movie->videos?->link, $matches);
                          $youtubeId = $matches[1] ?? null;
                        @endphp
                        <iframe width="360" height="240"
                            src="https://www.youtube.com/embed/{{ $youtubeId }}?autoplay=0&rel=0"
                            title="{{ $movie->name }}" frameborder="0"
                            allow="autoplay; encrypted-media" allowfullscreen>
                        </iframe>
                      </div>
                    </td>
                  </tr>
                  @else
                  <tr>
                    <td class="text-truncate">
                    <div class="d-flex align-items-center">
                      <span>{{ 'No Data Found' }}</span>
                    </div>
                    </td>
                  </tr>
                  @endif
                </tbody>
              </table>
              </div>
            </div>
          </div>
        </div>
        <div class="tab-pane fade" id="horizontal-budget">
          <div class="col-12">
            <div class="card overflow-hidden">
              <div class="table-responsive">
              <table class="table table-sm">
                <thead>
                <tr>
                  <th class="text-truncate">Date</th>
                  <th class="text-truncate">Panned Amount</th>
                  <th class="text-truncate">Budget</th>
                </tr>
                </thead>
                <tbody>
                  @if(!empty($movie->budget))
                  <tr>
                    <td class="text-truncate">
                    <div class="d-flex align-items-center">
                      <span>{{ $movie->budget->date }}</span>
                    </div>
                    </td>
                    <td class="text-truncate">
                      <div class="d-flex align-items-center">
                        <span>{{ $movie->budget->plan_amount }}</span>
                      </div>
                      </td>
                      <td class="text-truncate">
                        <div class="d-flex align-items-center">
                          <span>{{ $movie->budget->budget_amount }}</span>
                        </div>
                        </td>
                  </tr>
                  @else
                  <tr>
                    <td class="text-truncate">
                    <div class="d-flex align-items-center">
                      <span>{{ 'No Data Found' }}</span>
                    </div>
                    </td>
                  </tr>
                  @endif
                </tbody>
              </table>
              </div>
            </div>
          </div>
        </div>
        <div class="tab-pane fade" id="horizontal-finance">
          <div class="col-12">
            <div class="card overflow-hidden">
              <div class="table-responsive">
              <table class="table table-sm">
                <thead>
                <tr>
                  <th class="text-truncate">Additional Equity</th>
                  <th class="text-truncate">Co-Production Partner</th>
                  <th class="text-truncate">CA Tax Credit</th>
                  <th class="text-truncate">Post Financing</th>
                  <th class="text-truncate">Producers Dev. Funds</th>
                  <th class="text-truncate">Equity EP</th>
                  <th class="text-truncate">Domestic Pre Sales</th>
                </tr>
                </thead>
                <tbody>
                  @if(!empty($movie->finance))
                  <tr>
                    <td class="text-truncate">
                    <div class="d-flex align-items-center">
                      <span>{{ $movie->finance->additional_equity }}</span>
                    </div>
                    </td>
                    <td class="text-truncate">
                      <div class="d-flex align-items-center">
                        <span>{{ $movie->finance->coproduction_partner }}</span>
                      </div>
                    </td>
                    <td class="text-truncate">
                      <div class="d-flex align-items-center">
                        <span>{{ $movie->finance->ca_tax_credit }}</span>
                      </div>
                    </td>
                    <td class="text-truncate">
                      <div class="d-flex align-items-center">
                        <span>{{ $movie->finance->post_financing }}</span>
                      </div>
                    </td>
                    <td class="text-truncate">
                      <div class="d-flex align-items-center">
                        <span>{{ $movie->finance->producer_dev_fund }}</span>
                      </div>
                    </td>
                    <td class="text-truncate">
                      <div class="d-flex align-items-center">
                        <span>{{ $movie->finance->equity_ep }}</span>
                      </div>
                    </td>
                    <td class="text-truncate">
                      <div class="d-flex align-items-center">
                        <span>{{ $movie->finance->domestic_pre_sales }}</span>
                      </div>
                    </td>
                  </tr>
                  @else
                  <tr>
                    <td class="text-truncate">
                    <div class="d-flex align-items-center">
                      <span>{{ 'No Data Found' }}</span>
                    </div>
                    </td>
                  </tr>
                  @endif
                </tbody>
              </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--/ Custom content with heading -->
</div>
  </div>
</div>
@endsection