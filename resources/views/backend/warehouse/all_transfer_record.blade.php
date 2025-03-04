@extends('admin_dashboard')


@section('admin')
@section('title')
    Transfer Record | Pencil POS System
@endsection
<div class="content">

    <!-- Start Content-->
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            @if (Auth::user()->can('admin.manage'))
                                <a href="{{ route('export.daily.transfer') }}"
                                    class="btn btn-blue rounded-pill waves-effect waves-light">Export Daily</a>
                                &nbsp;
                                <a href="{{ route('export.weekly.transfer') }}"
                                    class="btn btn-blue rounded-pill waves-effect waves-light">Export Weekly</a>
                                &nbsp;
                            @endif

                        </ol>
                    </div>
                    <h4 class="page-title">ကုန်ပစ္စည်းလွှဲပြောင်းမှု စာရင်းများ </h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        {{-- <h4 style="font-size:30px;" align="center">Total :{{ count($product) }} Pcs</h4> --}}
                        <table id="basic-datatable" class="table dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>စဉ်</th>
                                    <th>Invoice</th>
                                    <th>From Shop</th>
                                    <th>To Shop</th>
                                    <th>Transfer Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                        <tbody>
                                @foreach ($transfers as $key => $item)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $item->invoice_no }}</td>
                                        <td>{{ $item->fromShop->name ?? 'N/A' }}</td>
                                        <td>{{ $item->toShop->name ?? 'N/A' }}</td>
                                        <td>{{ $item->date }}</td>

                                        <td>
                                            <a href="{{route('detail.transfer',$item->invoice_no)}}"
                                                class="btn btn-info sm" title="Detail"><i
                                                    class="fas fa-eye"></i></a>
                                        </td>
                                        {{-- <td>
                                            @if (Auth::user()->can('warehouse.delete'))
                                                <form action="{{ route('delete.transfer.record') }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    <input type="hidden" name="shop_id" value="{{ $item['shop_id'] }}">
                                                    <input type="hidden" name="product_id" value="{{ $item['product_id'] }}">
                                                    <input type="hidden" name="date" value="{{ $item['date'] }}">
                                                    <button type="submit" class="btn btn-danger sm" title="Delete Data">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </td> --}}
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
