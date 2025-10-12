<?php

namespace Tests\Unit;

use App\Http\Requests\StoreChurchRequest;
use App\Models\Church;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreChurchRequestTest extends TestCase
{

    /** @test */
    public function it_passes_validation_with_valid_data()
    {
        // Arrange
        $data = [
            'name' => 'Test Church',
            'abbreviation' => 'TC',
            'description' => 'Test description',
            'logo' => 'data:image/jpeg;base64,' . base64_encode('test-content')
        ];

        $request = new StoreChurchRequest();

        // Act
        $validator = Validator::make($data, $request->rules());

        // Assert
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function it_fails_validation_without_required_name()
    {
        // Arrange
        $data = [
            'abbreviation' => 'TC',
            'description' => 'Test description'
        ];

        $request = new StoreChurchRequest();

        // Act
        $validator = Validator::make($data, $request->rules());

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('name'));
    }

    /** @test */
    public function it_fails_validation_with_name_too_long()
    {
        // Arrange
        $data = [
            'name' => str_repeat('a', 256),
            'abbreviation' => 'TC',
            'description' => 'Test description'
        ];

        $request = new StoreChurchRequest();

        // Act
        $validator = Validator::make($data, $request->rules());

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('name'));
    }

    /** @test */
    public function it_fails_validation_with_abbreviation_too_long()
    {
        // Arrange
        $data = [
            'name' => 'Test Church',
            'abbreviation' => str_repeat('a', 11),
            'description' => 'Test description'
        ];

        $request = new StoreChurchRequest();

        // Act
        $validator = Validator::make($data, $request->rules());

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('abbreviation'));
    }

    /** @test */
    public function it_fails_validation_with_invalid_logo_format()
    {
        // Arrange
        $data = [
            'name' => 'Test Church',
            'abbreviation' => 'TC',
            'description' => 'Test description',
            'logo' => 'invalid-format'
        ];

        $request = new StoreChurchRequest();

        // Act
        $validator = Validator::make($data, $request->rules());

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('logo'));
    }

    /** @test */
    public function it_allows_nullable_fields()
    {
        // Arrange
        $data = [
            'name' => 'Test Church'
        ];

        $request = new StoreChurchRequest();

        // Act
        $validator = Validator::make($data, $request->rules());

        // Assert
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function it_has_correct_validation_messages()
    {
        // Arrange
        $request = new StoreChurchRequest();
        $messages = $request->messages();

        // Assert
        $this->assertEquals('Le nom de l\'église est requis.', $messages['name.required']);
        $this->assertEquals('Le nom de l\'église ne peut pas dépasser 255 caractères.', $messages['name.max']);
        $this->assertEquals('L\'abréviation ne peut pas dépasser 10 caractères.', $messages['abbreviation.max']);
        $this->assertEquals('Le format de l\'image logo n\'est pas valide (doit être en base64).', $messages['logo.regex']);
    }

    /** @test */
    public function it_authorizes_all_requests()
    {
        // Arrange
        $request = new StoreChurchRequest();

        // Act & Assert
        $this->assertTrue($request->authorize());
    }
}
