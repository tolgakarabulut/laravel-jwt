<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use JWTAuth;
use Hash;
use App\User;
use Tymon\JWTAuth\Exceptions\JWTException;

class MemberController extends Controller
{
    protected $data = [];

    /**
     *
     * @return void
     */
    public function __construct()
    {
        $this->data = [
            'status' => false,
            'code' => 401,
            'data' => null,
            'err' => [
                'code' => 1,
                'message' => 'Unauthorized'
            ]
        ];
    }

    /**
     * Request->only metodu ile sadece parametre olarak
     * email - password ü alıyoruz ve laravel içerisinde bulunan auth
     * metoduna ait olan attempt fonksiyonu ile kullanıcı doğrulamasını yapıyoruz.
     * kullanıcı doğru ise eğer  kullanıcıya sonraki işlemlerinde kullanabilmesi için
     * bir token dönüyoruz.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {

        $credentials = $request->only(['email', 'password']);
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                throw new Exception('invalid_credentials');
            }
            $this->data = [
                'status' => true,
                'code' => 200,
                'data' => [
                    '_token' => $token
                ],
                'err' => null
            ];

        } catch (Exception $e) {
            $this->data['err']['message'] = $e->getMessage();
            $this->data['code'] = 401;
        } catch (JWTException $e) {
            $this->data['err']['message'] = 'Could not create token';
            $this->data['code'] = 500;
        }
        return response()->json($this->data, $this->data['code']);
    }

    /**
     * Kullanıcı kayıt eden method burada kullanılan RegisterRequest  daha önce
     * anlatıldığı için detaylandırmıyorum.
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->post('name'),
            'email' => $request->post('email'),
            'password' => Hash::make($request->post('password'))
        ]);
        $this->data = [
            'status' => true,
            'code' => 200,
            'data' => [
                'User' => $user
            ],
            'err' => null
        ];
        return response()->json($this->data, $this->data['code']);
    }

    /**
     * Doğrulanmış olan kullanıcının detay bilgilerini getir.
     *
     * @return JsonResponse
     */
    public function detail(): JsonResponse
    {
        $this->data = [
            'status' => true,
            'code' => 200,
            'data' => [
                'User' => auth()->user()
            ],
            'err' => null
        ];
        return response()->json($this->data);
    }

    /**
     * Kullanıcının çıkış işlemini yap ve token'ı kullanılamaz duruma getir.
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        auth()->logout();
        $data = [
            'status' => true,
            'code' => 200,
            'data' => [
                'message' => 'Successfully logged out'
            ],
            'err' => null
        ];
        return response()->json($data);
    }

    /**
     * Son kullanma tarihi geçmiş olan JWT nin tekrar kullanılır hale gelmesi
     * için yenileme işlemi.
     *
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        $data = [
            'status' => true,
            'code' => 200,
            'data' => [
                '_token' => auth()->refresh()
            ],
            'err' => null
        ];
        return response()->json($data, 200);
    }

}
