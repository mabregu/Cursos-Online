<?php
namespace App\Traits\Teacher;

use App\Models\Coupon;
use App\Http\Requests\CouponRequest;

trait ManageCoupons {

    public function coupons()
    {
        $coupons = Coupon::forTeacher();
        return view('teacher.coupons.index', compact('coupons'));
    }

    public function createCoupon()
    {
        $coupon = new Coupon;
        $title = __("Crear un nuevo cupón");
        $textButton = __("Dar de alta el cupón");
        $options = ['route' => ['teacher.coupons.store']];
        return view('teacher.coupons.create', compact('title', 'coupon', 'options', 'textButton'));
    }

    public function storeCoupon(CouponRequest $request)
    {
        try {
            \DB::beginTransaction();
            $input = $this->couponInput();
            $coupon = Coupon::create($input);
            $coupon->courses()->sync(request("courses"), false);
            \DB::commit();
            session()->flash("message", ["success", __("Cupón creado satisfactoriamente")]);
            return redirect(route('teacher.coupons.edit', ['coupon' => $coupon]));
        } catch (\Throwable $exception) {
            \DB::rollBack();
            session()->flash("message", ["danger", $exception->getMessage()]);
            return back();
        }
    }

    public function editCoupon(Coupon $coupon) {
        $coupon->load("courses");
        $title = __("Editar el cupón :coupon", ["coupon" => $coupon->code]);
        $textButton = __("Actualizar cupón");
        $options = ['route' => ['teacher.coupons.update', ["coupon" => $coupon]]];
        $update = true;
        return view('teacher.coupons.edit', compact('title', 'coupon', 'options', 'textButton', 'update'));
    }

    public function updateCoupon(CouponRequest $request, Coupon $coupon) {
        try {
            \DB::beginTransaction();
            $input = $this->couponInput();
            $coupon->fill($input)->save();
            $coupon->courses()->sync(request("courses"));
            \DB::commit();
            session()->flash("message", ["success", __("Cupón actualizado satisfactoriamente")]);
            return redirect(route('teacher.coupons.edit', ['coupon' => $coupon]));
        } catch (\Throwable $exception) {
            \DB::rollBack();
            session()->flash("message", ["danger", $exception->getMessage()]);
            return back();
        }
    }

    public function destroyCoupon(Coupon $coupon) {
        if (request()->ajax()) {
            $coupon->delete();
            session()->flash("message", ["success", __("El cupón :code ha sido eliminado correctamente", [
                "code" => $coupon->code
            ])]);
        }
    }

    protected function couponInput(): array
    {
        return request()->only(
            "code",
            "description",
            "discount_type",
            "discount",
            "enabled",
            "expires_at"
        );
    }
}
