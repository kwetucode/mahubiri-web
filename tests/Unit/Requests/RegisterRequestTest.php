<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class RegisterRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer des rôles de test
        Role::factory()->create([
            'id' => 1,
            'name' => 'User',
            'slug' => 'user'
        ]);

        Role::factory()->create([
            'id' => 2,
            'name' => 'Admin',
            'slug' => 'admin'
        ]);

        // Créer un utilisateur existant pour tester l'unicité
        User::factory()->create([
            'email' => 'existing@example.com'
        ]);
    }

    public function test_register_request_authorizes_all_users()
    {
        $request = new RegisterRequest();
        $this->assertTrue($request->authorize());
    }

    public function test_register_request_validates_required_fields()
    {
        $request = new RegisterRequest();
        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        $this->assertFalse($validator->passes());
        $errors = $validator->errors()->toArray();

        $this->assertArrayHasKey('name', $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('password', $errors);
    }

    public function test_register_request_passes_with_valid_data()
    {
        $request = new RegisterRequest();
        $rules = $request->rules();

        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '+33123456789',
            'role_id' => 1
        ];

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_register_request_passes_without_optional_fields()
    {
        $request = new RegisterRequest();
        $rules = $request->rules();

        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_register_request_fails_with_invalid_email()
    {
        $request = new RegisterRequest();
        $rules = $request->rules();

        $data = [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_register_request_fails_with_existing_email()
    {
        $request = new RegisterRequest();
        $rules = $request->rules();

        $data = [
            'name' => 'Test User',
            'email' => 'existing@example.com', // Email déjà utilisé
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_register_request_fails_with_short_password()
    {
        $request = new RegisterRequest();
        $rules = $request->rules();

        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '123', // Trop court
            'password_confirmation' => '123'
        ];

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_register_request_fails_with_password_mismatch()
    {
        $request = new RegisterRequest();
        $rules = $request->rules();

        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password'
        ];

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_register_request_fails_with_long_name()
    {
        $request = new RegisterRequest();
        $rules = $request->rules();

        $data = [
            'name' => str_repeat('A', 256), // Trop long (>255 caractères)
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_register_request_fails_with_long_phone()
    {
        $request = new RegisterRequest();
        $rules = $request->rules();

        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => str_repeat('1', 21) // Trop long (>20 caractères)
        ];

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('phone', $validator->errors()->toArray());
    }

    public function test_register_request_fails_with_nonexistent_role()
    {
        $request = new RegisterRequest();
        $rules = $request->rules();

        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => 999 // N'existe pas
        ];

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('role_id', $validator->errors()->toArray());
    }

    public function test_register_request_passes_with_valid_role()
    {
        $request = new RegisterRequest();
        $rules = $request->rules();

        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => 2 // Rôle Admin existant
        ];

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_register_request_has_custom_messages()
    {
        $request = new RegisterRequest();
        $messages = $request->messages();

        $this->assertArrayHasKey('name.required', $messages);
        $this->assertArrayHasKey('email.required', $messages);
        $this->assertArrayHasKey('email.unique', $messages);
        $this->assertArrayHasKey('password.required', $messages);
        $this->assertArrayHasKey('password.min', $messages);
        $this->assertArrayHasKey('password.confirmed', $messages);

        // Vérifier que les messages sont en français
        $this->assertStringContainsString('obligatoire', $messages['name.required']);
        $this->assertStringContainsString('déjà utilisée', $messages['email.unique']);
    }

    public function test_register_request_has_custom_attributes()
    {
        $request = new RegisterRequest();
        $attributes = $request->attributes();

        $this->assertArrayHasKey('name', $attributes);
        $this->assertArrayHasKey('email', $attributes);
        $this->assertArrayHasKey('password', $attributes);
        $this->assertArrayHasKey('phone', $attributes);
        $this->assertArrayHasKey('role_id', $attributes);

        // Vérifier que les attributs sont en français
        $this->assertEquals('nom', $attributes['name']);
        $this->assertEquals('adresse email', $attributes['email']);
        $this->assertEquals('mot de passe', $attributes['password']);
    }

    public function test_register_request_validation_rules_structure()
    {
        $request = new RegisterRequest();
        $rules = $request->rules();

        // Vérifier la structure des règles
        $this->assertStringContainsString('required', $rules['name']);
        $this->assertStringContainsString('string', $rules['name']);
        $this->assertStringContainsString('max:255', $rules['name']);

        $this->assertStringContainsString('required', $rules['email']);
        $this->assertStringContainsString('email', $rules['email']);
        $this->assertStringContainsString('unique:users', $rules['email']);

        $this->assertStringContainsString('required', $rules['password']);
        $this->assertStringContainsString('min:8', $rules['password']);
        $this->assertStringContainsString('confirmed', $rules['password']);

        $this->assertStringContainsString('nullable', $rules['phone']);
        $this->assertStringContainsString('max:20', $rules['phone']);

        $this->assertStringContainsString('nullable', $rules['role_id']);
        $this->assertStringContainsString('exists:roles,id', $rules['role_id']);
    }
}
