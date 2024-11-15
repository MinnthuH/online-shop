@extends('admin_dashboard')


@section('admin')
@section('title')
    Shop Stock In Order | Pencil POS System
@endsection
<div class="content">

    <!-- Start Content-->
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box mb-2">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="{{ url('shop/stockin') }}"
                                class="btn btn-blue  waves-effect waves-light ">Cancel</a>
                            <span class="ms-2"></span><span></span>
                            <form action="{{ route('add.stockin.shop') }}" method="post">
                                @csrf
                                <input type="hidden" name="shopId" value="{{ $shop->id }}">
                                <button class="btn btn-blue waves-effect waves-light " type="submit">Import</button>
                            </form>

                            <span class="ms-2"></span><span></span>
                        </ol>


                    </div>
                    {{-- <h4 class="page-title">{{ $orgShopName->name ?? 'Unknown' }}<span> မှ </span>{{ $shop->name ?? 'Unknown' }} သို့  လွှဲပြောင်းမည့် ကုန်ပစ္စည်းစာရင်း</h4> --}}
                    @php
                    $totalItems = count($cartItem); // Count the total number of items
                @endphp
                <h4 class="page-title ms-5">Total Prodcuts: {{ $totalItems }}</h4>


                </div>

            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        <table id="basic-datatable" class="table dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Sl</th>
                                    <th class="text-wrap">ကုန်ပစ္စည်းအမည်</th>

                                    <th>အရေအတွက်</th>
                                </tr>
                            </thead>


                            <tbody>
                                @foreach ($cartItem as $key => $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <!-- Use $loop->iteration to get the current iteration number -->
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->qty }}</td>

                                    </tr>
                                @endforeach

                            </tbody>

                        </table>


                    </div> <!-- end card body-->
                </div> <!-- end card -->
            </div><!-- end col-->
        </div>
        <!-- end row-->


    </div> <!-- container -->

</div> <!-- content -->

@endsection