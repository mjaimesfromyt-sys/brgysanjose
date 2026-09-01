<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\ProfilePhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A real JPEG, drawn rather than faked, so the GD pipeline in ProfilePhoto
     * is genuinely exercised. Non-square on purpose: the crop is the part most
     * likely to break.
     */
    private function imageFile(int $width = 900, int $height = 600, string $name = 'selfie.jpg'): UploadedFile
    {
        $image = imagecreatetruecolor($width, $height);
        imagefilledrectangle($image, 0, 0, $width, $height, imagecolorallocate($image, 20, 120, 70));

        $path = tempnam(sys_get_temp_dir(), 'avatar') . '.jpg';
        imagejpeg($image, $path);
        imagedestroy($image);

        return new UploadedFile($path, $name, 'image/jpeg', null, true);
    }

    public function test_a_resident_can_open_their_profile(): void
    {
        $user = User::factory()->create(['first_name' => 'Juan', 'last_name' => 'Dela Cruz']);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('My Profile')
            ->assertSee('Juan');
    }

    public function test_the_profile_is_behind_authentication(): void
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
    }

    public function test_a_resident_can_update_their_details(): void
    {
        $user = User::factory()->create(['first_name' => 'Juan', 'contact_no' => null]);

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'first_name' => 'Juanito',
                'last_name'  => 'Dela Cruz',
                'contact_no' => '0917 555 1234',
                'address'    => 'Purok 5, San Jose',
                'purok'      => 'Purok 5',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('Juanito', $user->first_name);
        $this->assertSame('0917 555 1234', $user->contact_no);
        $this->assertSame('Purok 5, San Jose', $user->address);
    }

    public function test_role_and_status_cannot_be_escalated_through_the_profile_form(): void
    {
        $user = User::factory()->create(['role' => 'resident', 'status' => 'pending']);

        $this->actingAs($user)->put(route('profile.update'), [
            'first_name' => 'Juan',
            'last_name'  => 'Dela Cruz',
            'role'       => 'admin',
            'status'     => 'active',
        ]);

        $user->refresh();
        $this->assertSame('resident', $user->role);
        $this->assertSame('pending', $user->status);
    }

    public function test_a_resident_can_change_their_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password')]);

        $this->actingAs($user)
            ->put(route('profile.password'), [
                'current_password'      => 'old-password',
                'password'              => 'new-secret-password',
                'password_confirmation' => 'new-secret-password',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('new-secret-password', $user->refresh()->password));
    }

    public function test_the_password_change_requires_the_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password')]);

        $this->actingAs($user)
            ->put(route('profile.password'), [
                'current_password'      => 'wrong-password',
                'password'              => 'new-secret-password',
                'password_confirmation' => 'new-secret-password',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('old-password', $user->refresh()->password));
    }

    public function test_a_resident_can_upload_a_profile_picture(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('profile.photo.update'), ['photo' => $this->imageFile()])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue($user->hasAvatar());
        Storage::disk('local')->assertExists($user->avatar_path);
    }

    public function test_the_stored_photo_is_re_encoded_to_a_square_jpeg(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('profile.photo.update'), ['photo' => $this->imageFile(900, 600)]);

        $stored = Storage::disk('local')->get($user->refresh()->avatar_path);
        $size   = getimagesizefromstring($stored);

        $this->assertSame($size[0], $size[1], 'the stored photo should be square');
        $this->assertSame(IMAGETYPE_JPEG, $size[2]);
    }

    /**
     * The upload is decoded and re-encoded rather than stored as-is, so a file
     * that is a valid image with PHP appended cannot survive on disk.
     */
    public function test_content_smuggled_into_an_upload_does_not_reach_disk(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $file = $this->imageFile();
        file_put_contents($file->getRealPath(), '<?php echo "pwned"; ?>', FILE_APPEND);

        $this->actingAs($user)->post(route('profile.photo.update'), [
            'photo' => new UploadedFile($file->getRealPath(), 'selfie.jpg', 'image/jpeg', null, true),
        ]);

        $stored = Storage::disk('local')->get($user->refresh()->avatar_path);

        $this->assertStringNotContainsString('pwned', $stored);
        $this->assertStringNotContainsString('<?php', $stored);
    }

    public function test_a_non_image_is_rejected(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('profile.photo.update'), [
                'photo' => UploadedFile::fake()->create('resume.pdf', 40, 'application/pdf'),
            ])
            ->assertSessionHasErrors('photo');

        $this->assertFalse($user->refresh()->hasAvatar());
    }

    public function test_replacing_a_photo_deletes_the_previous_file(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('profile.photo.update'), ['photo' => $this->imageFile()]);
        $first = $user->refresh()->avatar_path;

        $this->actingAs($user)->post(route('profile.photo.update'), ['photo' => $this->imageFile()]);
        $second = $user->refresh()->avatar_path;

        $this->assertNotSame($first, $second);
        Storage::disk('local')->assertMissing($first);
        Storage::disk('local')->assertExists($second);
    }

    public function test_a_resident_can_remove_their_photo(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('profile.photo.update'), ['photo' => $this->imageFile()]);
        $path = $user->refresh()->avatar_path;

        $this->actingAs($user)->delete(route('profile.photo.destroy'))->assertRedirect(route('profile.edit'));

        $this->assertFalse($user->refresh()->hasAvatar());
        Storage::disk('local')->assertMissing($path);
    }

    public function test_a_resident_can_view_their_own_photo(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('profile.photo.update'), ['photo' => $this->imageFile()]);

        $this->actingAs($user)
            ->get(route('profile.photo', $user))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg');
    }

    public function test_one_resident_cannot_view_another_residents_photo(): void
    {
        Storage::fake('local');
        $owner   = User::factory()->create();
        $snooper = User::factory()->create();

        $this->actingAs($owner)->post(route('profile.photo.update'), ['photo' => $this->imageFile()]);

        $this->actingAs($snooper)->get(route('profile.photo', $owner))->assertForbidden();
    }

    public function test_barangay_staff_can_view_a_residents_photo(): void
    {
        Storage::fake('local');
        $resident = User::factory()->create();
        $admin    = User::factory()->admin()->create();

        $this->actingAs($resident)->post(route('profile.photo.update'), ['photo' => $this->imageFile()]);

        $this->actingAs($admin)->get(route('profile.photo', $resident))->assertOk();
    }

    public function test_a_missing_photo_is_a_404_rather_than_an_error(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('profile.photo', $user))->assertNotFound();
    }

    public function test_initials_fall_back_when_there_is_no_photo(): void
    {
        $user = User::factory()->create(['first_name' => 'Juan', 'last_name' => 'Dela Cruz']);

        $this->assertSame('JD', $user->initials);
        $this->assertFalse($user->hasAvatar());
    }

    public function test_an_oversized_image_is_refused_before_it_is_decoded(): void
    {
        $user = User::factory()->create();

        $this->expectException(\RuntimeException::class);

        // 8000 x 6000 = 48MP, past the guard, but written as a tiny file.
        ProfilePhoto::store($this->hugeButTinyFile(), $user);
    }

    /** A PNG whose header claims huge dimensions but which compresses to almost nothing. */
    private function hugeButTinyFile(): UploadedFile
    {
        $image = imagecreatetruecolor(8000, 6000);
        $path  = tempnam(sys_get_temp_dir(), 'huge') . '.png';
        imagepng($image, $path, 9);
        imagedestroy($image);

        return new UploadedFile($path, 'huge.png', 'image/png', null, true);
    }
}
