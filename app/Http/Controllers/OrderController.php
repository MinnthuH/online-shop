<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Transport;
use App\Models\OrderDetail;
use App\Models\ShopProduct;
use Illuminate\Http\Request;
use App\Models\TransportCharge;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Gloudemans\Shoppingcart\Facades\Cart;

class OrderController extends Controller
{
    // Final Invoice Method
    public function FinalInvoice(Request $request)
    {

        DB::beginTransaction();

        try {
            // Perform your database operations within the transaction
            // For example, inserting/updating records or deleting data

            $rtotal = $request->total;
            $rpay = $request->payNow;
            $invoiceNo = $this->generateInvoiceNumber();
            $shopId = Auth::user()->shop_id;

            $result = Sale::insertGetId([
                'user_id' => $request->userId,
                'shop_id' => $shopId,
                'customer_id' => $request->customerId,
                'deli_id' => $request->deliId,
                'invoice_date' => Carbon::now(),
                'invoice_no' => $invoiceNo,
                'payment_type' => $request->paymetnStatus,
                'sub_total' => $request->subTotal,
                'transprot_id' => $request->transportId,
                'total' => $request->total,
                'capital' => $request->capital,
                'discount' => $request->discount,
                'accepted_ammount' => $request->payNow,
                'due' => $request->due,
                'return_change' => $request->returnChange,
                'created_at' => Carbon::now()->setTimezone('Asia/Yangon'),
            ]);

            $data = array();
            $data['customer_id'] = $request->customerId;
            $data['user_id'] = $request->userId;
            $data['shop_id'] = $shopId;
            $data['order_date'] = $request->orderDate;
            $data['order_status'] = $request->orderStaus;
            $data['total_products'] = $request->porductCount;
            $data['sub_total'] = $request->subTotal;
            $data['capital'] = $request->capital;
            $data['invoice_no'] = $invoiceNo;
            $data['total'] = $request->total;
            $data['paymet_status'] = $request->paymetnStatus;
            $data['pay'] = $request->payNow;
            $data['due'] = $request->due;
            $data['created_at'] = Carbon::now();

            $sale_id = $result; //Retrieve the generated sale_id
            $order_id = Order::insertGetId($data);
            $contents = Cart::content();


            $pdata = array();
            foreach ($contents as $content) {
                $pdata['order_id'] = $order_id;
                $pdata['sale_id'] = $sale_id;
                $pdata['product_id'] = $content->id;
                $pdata['quantity'] = $content->qty;
                $pdata['unitcost'] = $content->price;
                $pdata['total'] = $content->price;
                $pdata['total'] = $content->total;
                $pdata['created_at'] = Carbon::now();

                OrderDetail::insert($pdata);
            } // end foreach

            foreach ($contents as $content) {
                // Retrieve ShopProduct record for the current product
                $shopProduct = ShopProduct::where('shop_id', $shopId)
                    ->where('product_id', $content->id)
                    ->first();

                if ($shopProduct) {
                    // Calculate the new quantity after subtracting sold quantity
                    $newQuantity = $shopProduct->quantity - $content->qty;

                    // Update the quantity in ShopProduct
                    $shopProduct->update(['quantity' => $newQuantity]);
                } else {
                    // Handle case where ShopProduct record doesn't exist (optional)
                    // This might occur if the product was not found in ShopProduct
                    // You can choose to log this or handle it based on your application logic
                    // For example, create a new ShopProduct record for the product
                }
            }
            // Commit the transaction if everything is successful
            DB::commit();
            // Additional code or redirect to a success page
            Cart::destroy();
            $returnChange = $request->returnChange;
            $customerId = $request->customerId;
            $customer = Customer::where('id', $customerId)->first();
            $shop = Shop::Where('id', $shopId)->first();

            $sale = Sale::latest()->firstOrFail();

            return view('backend.invoice.print_invoice_80mm', compact('sale', 'customer', 'rpay', 'returnChange', 'contents', 'shop'));
            // return view('backend.invoice.print_invoice_A5', compact('sale','customer','rpay','returnChange','contents','shop',''transport''));
            // return view('backend.invoice.print_invoice', compact('sale','customer','rpay','returnChange','contents','shop',''transport''));

        } catch (\Exception $e) {
            // An error occurred, so rollback the transaction

            DB::rollback();

            // Handle the error, log it, or redirect to an error page
            dd($e);
        }
    } // End Method

    // Pending Order Method
    public function PendingOrder()
    {

        $order = Order::where('order_status', 'pending')->get();
        return view('backend.order.pending_order', compact('order'));
    } // End Method

    // Detail Order Mehtod
    public function DetailOrder($id)
    {
        $order = Order::where('id', $id)->first();

        $orderItem = OrderDetail::with('product')->where('order_id', $id)->orderBy('id', 'DESC')->get();

        return view('backend.order.detail_order', compact('order', 'orderItem'));
    } //End Method

    // Update Order Status Method
    public function UpdateStatus(Request $request)
    {

        $order_id = $request->id;

        $product = OrderDetail::where('order_id', $order_id)->get();
        foreach ($product as $item) {
            Product::where('id', $item->product_id)
                ->update(['product_store' => DB::raw('product_store-' . $item->quantity)]);
        }

        Order::findOrFail($order_id)->update(['order_status' => 'complete']);

        $noti = [
            'message' => 'Order Done  Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->route('pending#order')->with($noti);
    } // End Method

    // Pending Order Method
    public function CompleteOrder()
    {
        $order = Order::where('order_status', 'complete')->get();
        return view('backend.order.complete_order', compact('order'));
    } // End Method

    // Manage Stock Method
    public function ManageStock()
    {

        $product = Product::latest()->get();
        return view('backend.stock.all_stock', compact('product'));
    } // End Method

    // Order Invoice Download
    public function InvoiceDownload($id)
    {

        $order = Order::where('id', $id)->first();

        $orderItem = OrderDetail::with('product')->where('order_id', $id)->orderBy('id', 'DESC')->get();

        $pdf = Pdf::loadView('backend.order.order_invoice', compact('order', 'orderItem'))->setPaper('a4')->setOption([
            'tempDir' => public_path(),
            'chroot' => public_path(),
        ]);
        return $pdf->download('invoice.pdf');
    } // End Method

    //////////// Due /////////////

    // pending due method
    public function PendingDue()
    {

        $alldue = Order::where('due', '>', '0')->orderBy('id', 'DESC')->get();
        return view('backend.order.pending_due', compact('alldue'));
    } // End Method

    // order due method
    public function OrderDueAjax($id)
    {
        $order = Order::findOrFail($id);
        return response()->json($order);
    } // End Method

    // Update Due Method
    public function UpdateDue(Request $request)
    {

        $orderId = $request->id;
        $payAmount = $request->pay;
        $dueAmmount = $request->due;

        $allOrder = Order::findOrFail($orderId);
        $mainDue = $allOrder->due;
        $mainPay = $allOrder->pay;

        $paidDue = $mainDue - $dueAmmount;
        $paidPay = $mainPay + $dueAmmount;

        Order::findOrFail($orderId)->update([
            'pay' => $paidPay,
            'due' => $paidDue,
        ]);

        $noti = [
            'message' => 'Due Paid  Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->route('pending#due')->with($noti);
    } // End Method

    ////////////////////////Private Funciton//////////////////////////
    private function generateInvoiceNumber()
    {
        // You can customize the format of the invoice number as per your requirements.
        // For example, you can use the date along with a random number or hash.

        $invoicePrefix = 'INV'; // You can set a prefix for the invoice number (e.g., 'INV' for Invoice).
        $randomNumberLength = 6; // You can set the desired length of the random number.

        $invoiceNumber = $invoicePrefix . '-' . date('Ymd') . '-' . mt_rand(100000, 999999);

        return $invoiceNumber;
    }
}
