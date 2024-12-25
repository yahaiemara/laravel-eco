@extends('layouts.admin')
@section('content')
<div class="main-content-inner">
    <!-- main-content-wrap -->
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>Slide</h3>
            <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                <li>
                    <a href="index.html">
                        <div class="text-tiny">Dashboard</div>
                    </a>
                </li>
                <li>
                    <i class="icon-chevron-right"></i>
                </li>
                <li>
                    <a href="slider.html">
                        <div class="text-tiny">Slider</div>
                    </a>
                </li>
                <li>
                    <i class="icon-chevron-right"></i>
                </li>
                <li>
                    <div class="text-tiny">New Slide</div>
                </li>
            </ul>
        </div>
        <!-- new-category -->
        <div class="wg-box">
            <form class="form-new-product form-style-1" action="{{route('slide.store')}}" method="POST" enctype="multipart/form-data">
               @csrf
                <fieldset class="name">
                    <div class="body-title">TagLine <span class="tf-color-1">*</span></div>
                    <input class="flex-grow" type="text" placeholder="TagLine" name="tagline" tabindex="0" value="{{old('tagline')}}" aria-required="true" required="">
                </fieldset>
                @error('tagline')
                 <p class="btn btn-danger">{{$messge}}</p>   
                @enderror
                <fieldset class="name">
                    <div class="body-title">Title <span class="tf-color-1">*</span></div>
                    <input class="flex-grow" type="text" placeholder="Line 1" name="title" tabindex="0" value="{{old('title')}}" aria-required="true" required="">
                </fieldset>
                @error('title')
                <p class="btn btn-danger">{{$messge}}</p>   
               @enderror
                <fieldset class="name">
                    <div class="body-title">Subtitle<span class="tf-color-1">*</span></div>
                    <input class="flex-grow" type="text" placeholder="Line 2" name="subtitle"  tabindex="0" value="{{old('subtitle')}}" aria-required="true" required="">
                       
                </fieldset>
                @error('subtitle')
                <p class="btn btn-danger">{{$messge}}</p>   
               @enderror
                <fieldset class="name">
                    <div class="body-title">Link<span class="tf-color-1">*</span></div>
                    <input class="flex-grow" type="text" placeholder="Line 2" name="link"  tabindex="0" value="{{old('link')}}" aria-required="true" required="">
                </fieldset>
                @error('link')
                <p class="btn btn-danger">{{$messge}}</p>   
               @enderror
                <fieldset>
                    <div class="body-title">Upload images <span class="tf-color-1">*</span>
                    </div>
                    <div id="imgpreview"  class="item" style="display:none">
                        <img width="500" src="" alt="" class="image">
                    </div>
                    <div class="upload-image flex-grow">
                        <div class="item up-load">
                            <label class="uploadfile" for="myFile">
                                <span class="icon">
                                    <i class="icon-upload-cloud"></i>
                                </span>
                                <span class="body-text">Drop your images here or select <span
                                        class="tf-color">click to browse</span></span>
                                <input type="file" id="myFile" name="image">
                            </label>
                        </div>
                    </div>
                </fieldset>
                <fieldset class="category">
                    <div class="body-title">Select category icon</div>
                    <div class="select flex-grow">
                        <select class="" name="status">
                            <option>Select icon</option>
                            <option value="1" @if(old('status')=='1') selected @endif>Active</option>
                            <option value="0" @if(old('status')=='0') selected @endif>In Active</option>
                        </select>
                    </div>
                </fieldset>
                <div class="bot">
                    <div></div>
                    <button class="tf-button w208" type="submit">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    $(function(){
    $("#myFile").on("change",function(e){
        const photoinp = $("#myFile");
        const [file] = this.files;
        if (file) {
            $("#imgpreview img").attr("src", URL.createObjectURL(file));
            $("#imgpreview").show();
        }
    });
});

</script>
@endpush