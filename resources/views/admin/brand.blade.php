@extends('layouts.admin')
@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>Brands</h3>
            <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                <li>
                    <a href="{{route('admin.index')}}">
                        <div class="text-tiny">Dashboard</div>
                    </a>
                </li>
                <li>
                    <i class="icon-chevron-right"></i>
                </li>
             
                <li>
                    <div class="text-tiny">Brands</div>
                </li>
            </ul>
        </div>

        <div class="wg-box">
            <div class="flex items-center justify-between gap10 flex-wrap">
                <div class="wg-filter flex-grow">
                    <form class="form-search">
                        <fieldset class="name">
                            <input type="text" placeholder="Search here..." class="" name="name"
                                tabindex="2" value="" aria-required="true" required="">
                        </fieldset>
                        <div class="button-submit">
                            <button class="" type="submit"><i class="icon-search"></i></button>
                        </div>
                    </form>
                </div>
                <a class="tf-button style-1 w208" href="{{route('brand_add')}}"><i
                        class="icon-plus"></i>Add new</a>
            </div>
            <div class="wg-table table-all-user">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                            
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Products</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($brand as $brands)
                                
                           
                            <tr>
                                <td>4</td>
                                <td class="pname">
                                    <div class="image">
                                        <img src="{{asset('/upload/brand')}}/{{$brands->image}}" alt="" class="image">
                                    </div>
                                    <div class="name">
                                        <a href="#" class="body-title-2">{{$brands->name}}</a>
                                    </div>
                                </td>
                                <td>{{$brands->slug}}</td>
                               
                                <td>
                                    <div class="list-icon-function">
                                        <a href="{{route('brand.edit',$brands->id)}}">
                                            <div class="item edit">
                                                <i class="icon-edit-3"></i>
                                            </div>
                                        </a>
                                        <form action="{{ route('brand.delete', $brands->id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="item text-danger delete">
                                                <i class="icon-trash-2"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="divider"></div>
                <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                {{$brand->links('pagination::bootstrap-5')}}
               
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<script>
$(function() {
    $('.delete').on('click', function(e) {
        e.preventDefault(); // منع الإجراء الافتراضي للنقر على الرابط.
        var form = $(this).closest('form'); // الحصول على الفورم الأقرب للزر الذي تم النقر عليه.
        
        // عرض رسالة تأكيد باستخدام SweetAlert
        swal({
            title: "Are you sure?",
            text: "You want to delete this record?",
            type: "warning", // نوع التنبيه (تحذير).
            buttons: ["No", "Yes"], // أزرار التنبيه.
            confirmButtonColor: '#dc3545' // لون زر التأكيد (مطابق للـ Bootstrap Danger).
        }).then(function(result) {
            if (result) {
                form.submit(); // إذا وافق المستخدم، يتم إرسال الفورم.
            }
        });
    });
});
</script>
    
@endpush