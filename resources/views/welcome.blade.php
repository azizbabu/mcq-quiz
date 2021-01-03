<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'MCQ Quiz') }}</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

        <!-- Styles -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
    </head>
    <body>
        <div class="container">
            <h1>MCQ Quiz</h1>
            <div class="question-area">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <ul class="list-unstyled">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session()->has('message') && session()->has('type'))
                    <div class="alert alert-{{ session('type') }} alert-dismissible fade show" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        {{ session('message') }}
                    </div>
                @endif

                <form action="{{ url('answer') }}" method="post">
                    @csrf
                    @forelse($questions as $question)
                        <div class="card mb-4{{ $loop->index ? ' d-none':'' }}" id="div-{{ $loop->index + 1 }}">
                            <div class="card-header">{{ $loop->index + 1 }}. {{ $question->title }}</div>
                            <div class="card-body">
                                @php $options = $question->options()->inRandomOrder()->get(); @endphp

                                @forelse($options as $option)
                                    <div class="option-area d-flex">
                                        <span class="d-inline-block me-2" style="margin-top:-2px">{{ $loop->index + 1 }}. </span>
                                        
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="option[{{ $question->id }}]" value="{{ $option->id }}" id="{{ $option->id }}" data-id="{{ $loop->parent->index + 1 }}">
                                            <label class="form-check-label" for="{{ $option->id }}">
                                                {{ $option->title }}
                                            </label>
                                        </div>
                                    </div>
                                @empty
                                @endforelse
                            </div>
                        </div>
                    @empty
                        <h2>No Data Found!</h2>
                    @endforelse

                    <div>
                        <button type="button" class="btn btn-info" data-id="1" id="later">Later</button>
                        <button type="button" class="btn btn-primary" id="next" data-id="1" disabled>Next</button>
                        <button class="btn btn-success d-none" id="submit">Submit</button>
                    </div>
                </form>
            </div>
        </div>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>
        <script>
            var maxq = {{ $questions->count() }};
            var skippedQuestionArr = []
            var crossLastElement = 0

            $('.form-check-input').click(function(e) {
                var id = parseInt($(this).data('id'));

                if(id == maxq) {
                    if(skippedQuestionArr.length == 0) {
                        $('#later').prop('disabled', true)
                        $('#submit').removeClass('d-none')
                    }else {
                        $('#next').prop('disabled', false)
                    }
                }else {
                    if(crossLastElement) {
                        if(skippedQuestionArr.length == 0) {
                            $('#later').prop('disabled', true)
                            $('#next').prop('disabled', true)
                            $('#submit').removeClass('d-none')
                        }else {
                            $('#next').prop('disabled', false)
                        }
                    }else {
                        $('#next').prop('disabled', false);
                    }
                }

                if(id < maxq && !crossLastElement) {
                    var next = (id+1);
                    $('#next').attr('data-id',next);
                    $('#later').attr('data-id',id);
                }
            });
            
            $('#next').click(function(e) {
                var id = parseInt($(this).attr('data-id'));
                
                $('.card').addClass('d-none');
                
                if(id < maxq) {
                    $('#div-'+id).removeClass('d-none');
                    var next = id < maxq ? id+1:id;
                    $(this).attr('data-id',next).prop('disabled', true);
                    $('#later').attr('data-id',id)
                }else {
                    if(!crossLastElement) {
                        crossLastElement = 1;
                        $('#div-'+id).removeClass('d-none');
                        var next = id < maxq ? id+1:id;
                        $(this).attr('data-id',next).prop('disabled', true);
                        $('#later').attr('data-id',id)
                    }else {
                        if(skippedQuestionArr.length) {
                            var id = skippedQuestionArr[0]
                            $('#div-'+id).removeClass('d-none');
                            $(this).attr('data-id',next).prop('disabled', true);
                            skippedQuestionArr.shift()
                        }
                    }
                    $('#later').prop('disabled', true);
                }
            });

            $('#later').click(function(e) {
                var id = parseInt($(this).attr('data-id'));
                skippedQuestionArr.push(id)
                $('.card').addClass('d-none');
                if(id < maxq) {
                    id++;
                }
                $('#div-'+id).removeClass('d-none');
                var next = id;
                $('#next').attr('data-id',next).prop('disabled', true);
                $(this).attr('data-id',next)

                if(id == maxq) {
                    $(this).prop('disabled', true);
                }
            });
        </script>
    </body>
</html>
