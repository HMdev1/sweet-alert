<?php

use Mockery as M;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use UxWeb\SweetAlert\SessionStore;
use UxWeb\SweetAlert\SweetAlertNotifier;

class SweetAlertNotifierTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private function assertArrayContainsSubset(array $expected, array $actual): void
    {
        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $actual);

            if (is_array($value)) {
                $this->assertIsArray($actual[$key]);
                $this->assertArrayContainsSubset($value, $actual[$key]);

                continue;
            }

            $this->assertSame($value, $actual[$key]);
        }
    }

    private function assertReceivedNonEmptyAlertPayload($session): void
    {
        $session->shouldHaveReceived('flash')->withArgs(function ($key, $value) {
            return $key === 'sweet_alert.alert' && is_string($value) && $value !== '';
        })->once();
    }

    #[Test]
    public function text_is_empty_by_default(): void
    {
        $session = M::spy(SessionStore::class);
        $notifier = new SweetAlertNotifier($session);

        $notifier->message();

        $this->assertSame('', $notifier->getConfig('text'));
    }

    #[Test]
    public function default_timer_is_2500_milliseconds(): void
    {
        $session = M::spy(SessionStore::class);
        $notifier = new SweetAlertNotifier($session);

        $notifier->message('Good News!');

        $this->assertSame(2500, $notifier->getConfig('timer'));
    }

    #[Test]
    public function buttons_config_is_false_by_default(): void
    {
        $session = M::spy(SessionStore::class);
        $notifier = new SweetAlertNotifier($session);

        $notifier->message('Good News!');

        $buttonsConfig = [
            'confirm' => false,
            'cancel' => false,
        ];

        $this->assertEquals($buttonsConfig, $notifier->getConfig('buttons'));
    }

    #[Test]
    public function first_parameter_of_alert_message_is_the_config_text(): void
    {
        $session = M::spy(SessionStore::class);
        $notifier = new SweetAlertNotifier($session);

        $notifier->message('Hello World!');

        $this->assertSame('Hello World!', $notifier->getConfig('text'));
    }

    #[Test]
    public function title_key_is_not_present_in_config_when_alert_title_is_not_set(): void
    {
        $session = M::spy(SessionStore::class);
        $notifier = new SweetAlertNotifier($session);

        $notifier->message('Hello World!');

        $this->assertArrayNotHasKey('title', $notifier->getConfig());
    }

    #[Test]
    public function second_parameter_of_alert_message_is_the_config_title(): void
    {
        $session = M::spy(SessionStore::class);
        $notifier = new SweetAlertNotifier($session);

        $notifier->message('Hello World!', 'This is the title');

        $this->assertSame('This is the title', $notifier->getConfig('title'));
    }

    #[Test]
    public function third_parameter_of_alert_message_is_the_config_icon(): void
    {
        $session = M::spy(SessionStore::class);
        $notifier = new SweetAlertNotifier($session);

        $notifier->message('Hello World!', 'This is the title', 'info');

        $this->assertSame('info', $notifier->getConfig('icon'));
    }

    #[Test]
    public function icon_key_is_not_present_in_config_when_alert_icon_is_not_set(): void
    {
        $session = M::spy(SessionStore::class);
        $notifier = new SweetAlertNotifier($session);

        $notifier->message('Hello World!', 'This is the title');

        $this->assertArrayNotHasKey('icon', $notifier->getConfig());
    }

    #[Test]
    public function it_flashes_config_for_a_basic_alert(): void
    {
        $session = M::spy(SessionStore::class);
        $notifier = new SweetAlertNotifier($session);

        $notifier->basic('Basic Alert!', 'Alert');

        $expectedConfig = [
            'text' => 'Basic Alert!',
            'title' => 'Alert',
        ];

        $this->assertArrayContainsSubset($expectedConfig, $notifier->getConfig());
        $session->shouldHaveReceived('flash')->with('sweet_alert.title', $expectedConfig['title'])->once();
        $session->shouldHaveReceived('flash')->with('sweet_alert.text', $expectedConfig['text'])->once();
        $this->assertReceivedNonEmptyAlertPayload($session);
    }

    #[Test]
    public function it_flashes_config_for_a_info_alert(): void
    {
        $session = M::spy(SessionStore::class);
        $notifier = new SweetAlertNotifier($session);

        $notifier->info('Info Alert!', 'Alert');

        $expectedConfig = [
            'text' => 'Info Alert!',
            'title' => 'Alert',
            'icon' => 'info',
        ];

        $this->assertArrayContainsSubset($expectedConfig, $notifier->getConfig());
        $session->shouldHaveReceived('flash')->with('sweet_alert.title', $expectedConfig['title'])->once();
        $session->shouldHaveReceived('flash')->with('sweet_alert.text', $expectedConfig['text'])->once();
        $session->shouldHaveReceived('flash')->with('sweet_alert.icon', $expectedConfig['icon'])->once();
        $this->assertReceivedNonEmptyAlertPayload($session);
    }

    #[Test]
    public function it_flashes_config_for_a_success_alert(): void
    {
        $session = M::spy(SessionStore::class);
        $notifier = new SweetAlertNotifier($session);

        $notifier->success('Well Done!', 'Success!');

        $expectedConfig = [
            'title' => 'Success!',
            'text' => 'Well Done!',
            'icon' => 'success',
        ];

        $this->assertArrayContainsSubset($expectedConfig, $notifier->getConfig());
        $session->shouldHaveReceived('flash')->with('sweet_alert.title', $expectedConfig['title'])->once();
        $session->shouldHaveReceived('flash')->with('sweet_alert.text', $expectedConfig['text'])->once();
        $session->shouldHaveReceived('flash')->with('sweet_alert.icon', $expectedConfig['icon'])->once();
        $this->assertReceivedNonEmptyAlertPayload($session);
    }

    #[Test]
    public function it_flashes_config_for_a_warning_alert(): void
    {
        $session = M::spy(SessionStore::class);
        $notifier = new SweetAlertNotifier($session);

        $notifier->warning('Hey cowboy!', 'Watch Out!');

        $expectedConfig = [
            'title' => 'Watch Out!',
            'text' => 'Hey cowboy!',
            'icon' => 'warning',
        ];

        $this->assertArrayContainsSubset($expectedConfig, $notifier->getConfig());
        $session->shouldHaveReceived('flash')->with('sweet_alert.title', $expectedConfig['title'])->once();
        $session->shouldHaveReceived('flash')->with('sweet_alert.text', $expectedConfig['text'])->once();
        $session->shouldHaveReceived('flash')->with('sweet_alert.icon', $expectedConfig['icon'])->once();
        $this->assertReceivedNonEmptyAlertPayload($session);
    }

    #[Test]
    public function it_flashes_config_for_a_error_alert(): void
    {
        $session = M::spy(SessionStore::class);
        $notifier = new SweetAlertNotifier($session);

        $notifier->error('Something wrong happened!', 'Whoops!');

        $expectedConfig = [
            'title' => 'Whoops!',
            'text' => 'Something wrong happened!',
            'icon' => 'error',
        ];

        $this->assertArrayContainsSubset($expectedConfig, $notifier->getConfig());
        $session->shouldHaveReceived('flash')->with('sweet_alert.title', $expectedConfig['title'])->once();
        $session->shouldHaveReceived('flash')->with('sweet_alert.text', $expectedConfig['text'])->once();
        $session->shouldHaveReceived('flash')->with('sweet_alert.icon', $expectedConfig['icon'])->once();
        $this->assertReceivedNonEmptyAlertPayload($session);
    }

    #[Test]
    public function autoclose_can_be_customized_for_an_alert_message(): void
    {
        $session = M::spy(SessionStore::class);
        $notifier = new SweetAlertNotifier($session);

        $notifier->message('Hello!', 'Alert')->autoclose(2000);

        $this->assertSame(2000, $notifier->getConfig('timer'));
        unset($notifier);
        $session->shouldHaveReceived('flash')->with('sweet_alert.timer', 2000);
    }

    #[Test]
    public function timer_option_is_not_present_in_config_when_using_a_persistent_alert(): void
    {
        $session = M::mock(SessionStore::class);
        $session->shouldReceive('flash')->atLeast()->once();
        $session->shouldReceive('remove')->atLeast()->once();
        $notifier = new SweetAlertNotifier($session);

        $notifier->message('Please, read with care!', 'Alert')->persistent('Got it!');

        $this->assertArrayNotHasKey('timer', $notifier->getConfig());
    }

    #[Test]
    public function persistent_alert_has_only_a_confirm_button_by_default(): void
    {
        $session = M::mock(SessionStore::class);
        $session->shouldReceive('flash')->atLeast()->once();
        $session->shouldReceive('remove')->atLeast()->once();
        $notifier = new SweetAlertNotifier($session);

        $notifier->warning('Are you sure?', 'Delete all posts')->persistent('I\'m sure');

        $this->assertArrayContainsSubset(
            [
                'confirm' => [
                    'text' => 'I\'m sure',
                    'visible' => true,
                ],
            ],
            $notifier->getConfig('buttons')
        );
    }

    #[Test]
    public function it_will_add_the_content_option_to_config_when_using_an_html_alert(): void
    {
        $session = M::mock(SessionStore::class);
        $session->shouldReceive('flash')->atLeast()->once();
        $session->shouldReceive('remove')->atLeast()->once();
        $notifier = new SweetAlertNotifier($session);

        $notifier->message('<strong>This should be bold!</strong>', 'Alert')->html();

        $this->assertSame('<strong>This should be bold!</strong>', $notifier->getConfig('content'));
    }

    #[Test]
    public function allows_to_configure_a_confirm_button_for_an_alert(): void
    {
        $session = M::mock(SessionStore::class);
        $session->shouldReceive('flash')->atLeast()->once();
        $session->shouldReceive('remove')->atLeast()->once();
        $notifier = new SweetAlertNotifier($session);

        $notifier->basic('Basic Alert!', 'Alert')->confirmButton('help!');

        $this->assertArrayContainsSubset(
            [
                'text' => 'help!',
                'visible' => true,
            ],
            $notifier->getConfig('buttons')['confirm']
        );
        $this->assertFalse($notifier->getConfig('closeOnClickOutside'));
    }

    #[Test]
    public function allows_to_configure_a_cancel_button_for_an_alert(): void
    {
        $session = M::spy(SessionStore::class);
        $notifier = new SweetAlertNotifier($session);

        $notifier->basic('Basic Alert!', 'Alert')->cancelButton('Cancel!');

        $this->assertArrayContainsSubset(['text' => 'Cancel!', 'visible' => true], $notifier->getConfig('buttons')['cancel']);
        $this->assertFalse($notifier->getConfig('closeOnClickOutside'));
    }

    #[Test]
    public function close_on_click_outside_config_can_be_enabled(): void
    {
        $session = M::spy(SessionStore::class);
        $notifier = new SweetAlertNotifier($session);

        $notifier->basic('Basic Alert!', 'Alert')->closeOnClickOutside();

        $this->assertTrue($notifier->getConfig('closeOnClickOutside'));
    }

    #[Test]
    public function close_on_click_outside_config_can_be_disabled(): void
    {
        $session = M::spy(SessionStore::class);
        $notifier = new SweetAlertNotifier($session);

        $notifier->basic('Basic Alert!', 'Alert')->closeOnClickOutside(false);

        $this->assertFalse($notifier->getConfig('closeOnClickOutside'));
    }

    #[Test]
    public function additional_buttons_can_be_added(): void
    {
        $session = M::spy(SessionStore::class);
        $notifier = new SweetAlertNotifier($session);

        $notifier->basic('Pay with:', 'Payment')->addButton('credit_card', 'Credit Card');
        $notifier->basic('Pay with:', 'Payment')->addButton('paypal', 'Paypal');

        $this->assertArrayContainsSubset(
            [
                'credit_card' => [
                    'text' => 'Credit Card',
                    'visible' => true,
                ],
                'paypal' => [
                    'text' => 'Paypal',
                    'visible' => true,
                ],
            ],
            $notifier->getConfig('buttons')
        );
        $this->assertFalse($notifier->getConfig('closeOnClickOutside'));
    }

    #[Test]
    public function additional_config_can_be_added_to_configure_alert_message(): void
    {
        $session = M::spy(SessionStore::class);
        $notifier = new SweetAlertNotifier($session);

        $notifier->basic('Basic Alert!', 'Alert')->setConfig(['dangerMode' => true]);

        $this->assertTrue($notifier->getConfig('dangerMode'));
        unset($notifier);
        $session->shouldHaveReceived('flash')->with('sweet_alert.dangerMode', true);
    }
}

function config($key = null, $default = null)
{
    return 2500;
}
