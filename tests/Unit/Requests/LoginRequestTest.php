<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\LoginRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class LoginRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer un utilisateur de test
        Role::factory()->create(['id' => 1]);
        User::factory()->create([
            'email' => 'test@example.com',
            'phone' => '+33123456789'
        ]);
    }

    public function test_login_request_authorizes_all_users()
    {
        $request = new LoginRequest();
        $this->assertTrue($request->authorize());
    }

    public function test_login_request_validates_required_fields()
    {
        $request = new LoginRequest();
        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('login', $validator->errors()->toArray());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_login_request_passes_with_valid_email()
    {
        $request = new LoginRequest();
        $rules = $request->rules();

        $data = [
            'login' => 'test@example.com',
            'password' => 'password123'
        ];

        $validator = Validator::make($data, $rules);
        $request->setValidator($validator);

        // Simuler la méthode input pour les tests
        $request = new class extends LoginRequest {
            private $data;

            public function setData($data)
            {
                $this->data = $data;
            }

            public function input($key = null, $default = null)
            {
                return $key ? ($this->data[$key] ?? $default) : $this->data;
            }
        };

        $request->setData($data);

        $this->assertTrue($validator->passes());
        $this->assertTrue($request->isEmail());
        $this->assertEquals('email', $request->getLoginField());
    }

    public function test_login_request_passes_with_valid_phone()
    {
        $request = new LoginRequest();
        $rules = $request->rules();

        $data = [
            'login' => '+33123456789',
            'password' => 'password123'
        ];

        $validator = Validator::make($data, $rules);

        $request = new class extends LoginRequest {
            private $data;

            public function setData($data)
            {
                $this->data = $data;
            }

            public function input($key = null, $default = null)
            {
                return $key ? ($this->data[$key] ?? $default) : $this->data;
            }
        };

        $request->setData($data);

        $this->assertTrue($validator->passes());
        $this->assertFalse($request->isEmail());
        $this->assertEquals('phone', $request->getLoginField());
    }

    public function test_login_request_fails_with_empty_login()
    {
        $request = new LoginRequest();
        $rules = $request->rules();

        $data = [
            'login' => '',
            'password' => 'password123'
        ];

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('login', $validator->errors()->toArray());
    }

    public function test_login_request_fails_with_empty_password()
    {
        $request = new LoginRequest();
        $rules = $request->rules();

        $data = [
            'login' => 'test@example.com',
            'password' => ''
        ];

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_login_request_has_custom_messages()
    {
        $request = new LoginRequest();
        $messages = $request->messages();

        $this->assertArrayHasKey('login.required', $messages);
        $this->assertArrayHasKey('password.required', $messages);
        $this->assertStringContainsString('email ou', $messages['login.required']);
    }

    public function test_login_request_has_custom_attributes()
    {
        $request = new LoginRequest();
        $attributes = $request->attributes();

        $this->assertArrayHasKey('login', $attributes);
        $this->assertArrayHasKey('password', $attributes);
        $this->assertStringContainsString('identifiant', $attributes['login']);
    }

    public function test_is_email_method_correctly_identifies_email()
    {
        $request = new class extends LoginRequest {
            private $data;

            public function setData($data)
            {
                $this->data = $data;
            }

            public function input($key = null, $default = null)
            {
                return $key ? ($this->data[$key] ?? $default) : $this->data;
            }
        };

        // Test avec email valide
        $request->setData(['login' => 'test@example.com']);
        $this->assertTrue($request->isEmail());

        // Test avec téléphone
        $request->setData(['login' => '+33123456789']);
        $this->assertFalse($request->isEmail());

        // Test avec chaîne quelconque
        $request->setData(['login' => 'not-an-email']);
        $this->assertFalse($request->isEmail());
    }

    public function test_get_login_field_returns_correct_field()
    {
        $request = new class extends LoginRequest {
            private $data;

            public function setData($data)
            {
                $this->data = $data;
            }

            public function input($key = null, $default = null)
            {
                return $key ? ($this->data[$key] ?? $default) : $this->data;
            }
        };

        // Test avec email
        $request->setData(['login' => 'test@example.com']);
        $this->assertEquals('email', $request->getLoginField());

        // Test avec téléphone
        $request->setData(['login' => '+33123456789']);
        $this->assertEquals('phone', $request->getLoginField());
    }
}
