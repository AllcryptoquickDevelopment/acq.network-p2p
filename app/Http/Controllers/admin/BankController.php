<?php

namespace App\Http\Controllers\admin;

use App\Http\Requests\Admin\BankRequest;
use App\Http\Services\CommonService;
use App\Model\Bank;
use App\Repository\BankRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BankController extends Controller
{
    /*
    *
    * bank List
    * Show the list of specified resource.
    * @return \Illuminate\Http\Response
    *
    */
    public function bankList(Request $request)
    {
        $data['title'] = __('Bank List');
        $data['menu'] = 'bank';
        if ($request->ajax()) {
            $items = Bank::select('*')->where('status','<>', 5);
            return datatables($items)
                ->addColumn('status', function ($item) {
                    return status($item->status);
                })
                ->addColumn('country', function ($item) {
                    return !empty($item->country) ? countrylist($item->country) : '';
                })
                ->editColumn('created_at', function ($item) {
                    return $item->created_at ? with(new Carbon($item->created_at))->format('d M Y') : '';
                })
                ->filterColumn('created_at', function ($query, $keyword) {
                    $query->whereRaw("DATE_FORMAT(created_at,'%d %M %Y') like ?", ["%$keyword%"]);
                })
                ->addColumn('activity', function ($item) {
                    $html = '<ul class="d-flex activity-menu">';
                    $html .= edit_html('bankEdit', $item->unique_code);
                    $html .= delete_html('bankDelete', ($item->unique_code));
                    $html .= '</ul>';
                    return $html;
                })
                ->rawColumns(['activity'])
                ->make(true);
        }

        return view('admin.bank.list', $data);
    }

    /*
     * bankAdd
     *
     *
     * Show the form for creating a new resource.
     * @return \Illuminate\Http\Response
     *
     *
     *
     */

    public function bankAdd()
    {
        $data['title'] = __('Add new bank');
        $data['menu'] = 'bank';

        return view('admin.bank.addEdit', $data);
    }

    /**
     * bankEdit
     *
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function bankEdit($id)
    {
        $data['title'] = __('Update Bank');
        $data['menu'] = 'bank';

        $data['item'] = Bank::where('unique_code',$id)->first();

        return view('admin.bank.addEdit', $data);
    }

    /**
     * bankAddProcess
     *
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function bankAddProcess(BankRequest $request)
    {
        if ($request->isMethod('post')) {
            $response = app(BankRepository::class)->bankSaveProcess($request);
            if ($response['success'] == true) {
                return redirect()->route('bankList')->with('success', $response['message']);
            }

            return redirect()->back()->withInput()->with('dismiss', $response['message']);
        }
        return redirect()->back();
    }

    /**
     * bankDelete
     *
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     *
     */
    public function bankDelete($id)
    {
        if (isset($id)) {

            $response = app(BankRepository::class)->deleteBank($id);
            if ($response['success'] == true) {
                return redirect()->route('bankList')->with('success', $response['message']);
            }

            return redirect()->back()->withInput()->with('dismiss', $response['message']);
        }
        return redirect()->back();
    }
}
