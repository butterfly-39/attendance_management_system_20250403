<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class AttendanceControllerTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        // テストユーザーを作成
        $this->user = User::factory()->create([
            'is_admin' => false
        ]);

        // 基準となる日時を設定
        Carbon::setTestNow(Carbon::create(2024, 1, 1, 9, 0, 0));
    }

    /** @test */
    public function 現在の日時情報がUIと同じ形式で出力されている()
    {
        $response = $this->actingAs($this->user)
            ->get('/attendance');

        $response->assertStatus(200)
            ->assertSee(Carbon::now()->format('Y年n月j日'))
            ->assertSee(Carbon::now()->format('H:i'));
    }

    /** @test */
    public function 勤務外の場合勤怠ステータスが正しく表示される()
    {
        $response = $this->actingAs($this->user)
            ->get('/attendance');

        $response->assertStatus(200)
            ->assertViewHas('attendance', null);
    }

    /** @test */
    public function 出勤中の場合勤怠ステータスが正しく表示される()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::today(),
            'status' => '出勤中'
        ]);

        $response = $this->actingAs($this->user)
            ->get('/attendance');

        $response->assertStatus(200)
            ->assertSee('出勤中');
    }

    /** @test */
    public function 休憩中の場合勤怠ステータスが正しく表示される()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::today(),
            'status' => '休憩中'
        ]);

        $response = $this->actingAs($this->user)
            ->get('/attendance');

        $response->assertStatus(200)
            ->assertSee('休憩中');
    }

    /** @test */
    public function 退勤済の場合勤怠ステータスが正しく表示される()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::today(),
            'status' => '退勤済'
        ]);

        $response = $this->actingAs($this->user)
            ->get('/attendance');

        $response->assertStatus(200)
            ->assertSee('退勤済');
    }

    /** @test */
    public function 出勤ボタンが正しく機能する()
    {
        $response = $this->actingAs($this->user)
            ->post('/attendance', ['action' => 'clock_in']);

        $response->assertStatus(302);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->user->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'status' => '出勤中'
        ]);
    }

    /** @test */
    public function 出勤は一日一回のみできる()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::today(),
            'status' => '退勤済'
        ]);

        $response = $this->actingAs($this->user)
            ->get('/attendance');

        $response->assertStatus(200)
            ->assertDontSee('出勤する')
            ->assertSee('お疲れ様でした');
    }

    /** @test */
    public function 休憩ボタンが正しく機能する()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::today(),
            'status' => '出勤中'
        ]);

        $response = $this->actingAs($this->user)
            ->post('/attendance', ['action' => 'break_start']);

        $response->assertStatus(302);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->user->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'status' => '休憩中'
        ]);
    }

    /** @test */
    public function 休憩戻りボタンが正しく機能する()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::today(),
            'status' => '休憩中'
        ]);

        $breakTime = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start_time' => Carbon::now()->subHour(),
            'break_end_time' => null
        ]);

        $response = $this->actingAs($this->user)
            ->post('/attendance', ['action' => 'break_end']);

        $response->assertStatus(302);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->user->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'status' => '出勤中'
        ]);
    }

    /** @test */
    public function 退勤ボタンが正しく機能する()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::today(),
            'status' => '出勤中'
        ]);

        $response = $this->actingAs($this->user)
            ->post('/attendance', ['action' => 'clock_out']);

        $response->assertStatus(302);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->user->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'status' => '退勤済'
        ]);
    }

    /** @test */
    public function 勤怠一覧画面で自分の勤怠情報が全て表示される()
    {
        $attendances = Attendance::factory()->count(3)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->get('/attendance/list');

        $response->assertStatus(200);
        foreach ($attendances as $attendance) {
            $response->assertSee(Carbon::parse($attendance->date)->format('m/d'));
        }
    }

    /** @test */
    public function 勤怠詳細画面で正しい情報が表示される()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::today(),
            'clock_in_time' => Carbon::now()->subHours(8),
            'clock_out_time' => Carbon::now()
        ]);

        $breakTime = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start_time' => Carbon::now()->subHours(4),
            'break_end_time' => Carbon::now()->subHours(3)
        ]);

        $response = $this->actingAs($this->user)
            ->get("/attendance/{$attendance->id}");

        $response->assertStatus(200)
            ->assertSee($this->user->name)
            ->assertSee(Carbon::parse($attendance->date)->format('Y年'))
            ->assertSee(Carbon::parse($attendance->date)->format('n月j日'))
            ->assertSee($attendance->clock_in_time->format('H:i'))
            ->assertSee($attendance->clock_out_time->format('H:i'))
            ->assertSee($breakTime->break_start_time->format('H:i'))
            ->assertSee($breakTime->break_end_time->format('H:i'));
    }

    /** @test */
    public function 出勤時間が退勤時間より後の場合エラーメッセージが表示される()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()
        ]);

        $response = $this->actingAs($this->user)
            ->from("/attendance/{$attendance->id}")
            ->put("/attendance/{$attendance->id}", [
                'clock_in_time' => '17:00',
                'clock_out_time' => '16:00',
                'break_start_time' => [],
                'break_end_time' => [],
                'note' => '修正理由'
            ]);

        $response->assertRedirect()
            ->assertSessionHasErrors(['clock_out_time' => '出勤時間もしくは退勤時間が不適切な値です']);
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後の場合エラーメッセージが表示される()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()
        ]);

        $response = $this->actingAs($this->user)
            ->from("/attendance/{$attendance->id}")
            ->put("/attendance/{$attendance->id}", [
                'clock_in_time' => '09:00',
                'clock_out_time' => '17:00',
                'break_start_time' => ['18:00'],
                'break_end_time' => ['19:00'],
                'note' => '修正理由'
            ]);

        $response->assertSessionHasErrors(['break_start_time.0' => '休憩時間が勤務時間外です']);
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後の場合エラーメッセージが表示される()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()
        ]);

        $response = $this->actingAs($this->user)
            ->put("/attendance/{$attendance->id}", [
                'clock_in_time' => '09:00',
                'clock_out_time' => '17:00',
                'break_start_time' => ['12:00'],
                'break_end_time' => ['18:00'],
                'note' => '修正理由'
            ]);

        $response->assertSessionHasErrors(['break_end_time.0' => '休憩時間が勤務時間外です']);
    }

    /** @test */
    public function 備考欄が未入力の場合エラーメッセージが表示される()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()
        ]);

        $response = $this->actingAs($this->user)
            ->put("/attendance/{$attendance->id}", [
                'clock_in_time' => '09:00',
                'clock_out_time' => '17:00',
                'break_start_time' => ['12:00'],
                'break_end_time' => ['13:00'],
                'note' => ''
            ]);

        $response->assertSessionHasErrors(['note' => '備考を記入してください']);
    }

    /** @test */
    public function 修正申請処理が正しく実行される()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()
        ]);

        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($this->user)
            ->from("/attendance/{$attendance->id}")
            ->put("/attendance/{$attendance->id}", [
                'clock_in_time' => '09:00',
                'clock_out_time' => '17:00',
                'break_start_time' => ['12:00'],
                'break_end_time' => ['13:00'],
                'note' => '修正理由'
            ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('stamp_correction_requests', [
            'attendance_id' => $attendance->id,
            'status' => '承認待ち',
            'note' => '修正理由'
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/stamp_correction_request/list');

        $response->assertStatus(200)
            ->assertSee('修正...');
    }

    /** @test */
    public function 休憩修正申請処理が正しく実行される()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()
        ]);

        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($this->user)
            ->from("/attendance/{$attendance->id}")
            ->put("/attendance/{$attendance->id}", [
                'break_start_time' => ['12:00'],
                'break_end_time' => ['13:00'],
                'note' => '休憩修正理由'
            ]);

        $response->assertStatus(302);

        $stampCorrectionRequest = \App\Models\StampCorrectionRequest::where('attendance_id', $attendance->id)->first();

        $this->assertDatabaseHas('break_correction_requests', [
            'stamp_correction_request_id' => $stampCorrectionRequest->id,
            'break_start_time' => '2024-01-01 12:00:00',
            'break_end_time' => '2024-01-01 13:00:00'
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/stamp_correction_request/list');

        $response->assertStatus(200)
            ->assertSee('休憩...');
    }
}
