<?php

use Tamtamchik\SimpleFlash\Exceptions\FlashSingletonException;
use Tamtamchik\SimpleFlash\Exceptions\FlashTemplateException;
use Tamtamchik\SimpleFlash\Exceptions\FlashTemplateNotFoundException;
use Tamtamchik\SimpleFlash\Flash;
use Tamtamchik\SimpleFlash\TemplateFactory;
use Tamtamchik\SimpleFlash\Templates;

session_start();

require_once __DIR__ . '/../vendor/autoload.php';
require_once 'BadTemplate.php';

class FlashTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function testStaticCall()
    {
        Flash::message('Static message');

        $this->assertNotEmpty(Flash::display());
    }

    /** @test */
    public function testCreation()
    {
        $flash = new Flash();

        $this->assertFalse($flash->hasMessages());
        $this->assertEquals('Tamtamchik\SimpleFlash\Flash', get_class($flash));
    }

    /** @test */
    public function testFunction()
    {
        try {
            $flash = flash();
        } catch (FlashTemplateNotFoundException $e) {
            $this->assertFalse(true); // should fail the test
        }

        $this->assertFalse($flash->hasMessages());
        $this->assertEquals('Tamtamchik\SimpleFlash\Flash', get_class($flash));
    }

    /** @test */
    public function testMessageWorkflow()
    {
        try {
            $flash = flash('Test info message');
        } catch (FlashTemplateNotFoundException $e) {
            $this->assertFalse(true); // should fail the test
        }

        $this->assertTrue($flash->hasMessages());
        $this->assertContains('Test info message', $flash->display());
        $this->assertFalse($flash->hasMessages());
    }

    /** @test */
    public function testFunctionMessageType()
    {
        try {
            $flash = flash('Test info message', 'success');
        } catch (FlashTemplateNotFoundException $e) {
            $this->assertFalse(true); // should fail the test
        }

        $this->assertContains('success', $flash->display());
    }

    /** @test */
    public function testChaining()
    {
        try {
            $flash = flash()->message('Test info message 1')->message('Test info message 2');
        } catch (FlashTemplateNotFoundException $e) {
            $this->assertFalse(true); // should fail the test
        }

        $content = $flash->display();
        $this->assertContains('Test info message 1', $content);
        $this->assertContains('Test info message 2', $content);
    }

    /** @test */
    public function testInfoDefaultMessage()
    {
        try {
            $flash = flash('Test info message');
        } catch (FlashTemplateNotFoundException $e) {
            $this->assertFalse(true); // should fail the test
        }

        $this->assertContains('info', $flash->display());
    }

    /** @test */
    public function testMessageTypes()
    {
        try {
            $flash = flash()
                ->message('Dummy 1', 'success')
                ->message('Dummy 2', 'info')
                ->message('Dummy 2', 'warning')
                ->message('Dummy 2', 'error');
        } catch (FlashTemplateNotFoundException $e) {
            $this->assertFalse(true); // should fail the test
        }

        $content = $flash->display();
        $this->assertContains('success', $content);
        $this->assertContains('info', $content);
        $this->assertContains('success', $content);
        $this->assertContains('danger', $content);
    }

    /** @test */
    public function testPartialDisplay()
    {
        try {
            $flash = flash()->message('Dummy 1', 'success')->message('Dummy 2');
        } catch (FlashTemplateNotFoundException $e) {
            $this->assertFalse(true); // should fail the test
        }

        $this->assertTrue($flash->hasMessages('success'));

        $content = $flash->display('success');

        $this->assertContains('Dummy 1', $content);
        $this->assertNotContains('Dummy 2', $content);
    }

    /** @test */
    public function testWrongDisplays()
    {
        try {
            $flash = flash()->message('Dummy 1', 'success')->message('Dummy 2');
        } catch (FlashTemplateNotFoundException $e) {
            $this->assertFalse(true); // should fail the test
        }

        $this->assertFalse($flash->hasMessages('wrong'));

        $content = $flash->display('wrong');

        $this->assertEmpty($content);
    }

    /** @test */
    public function testAccessAsString()
    {
        $flash = new Flash();
        $flash->clear();

        $flash->message('Test message');
        $this->assertContains('Test message', "{$flash}");
    }

    /** @test */
    public function testWrongMessageType()
    {
        try {
            $flash = flash();
            $flash->message('Test message', 'bad');
            $this->assertFalse(flash()->hasMessages());
        } catch (FlashTemplateNotFoundException $e) {
            $this->assertFalse(true); // should fail the test
        }
    }

    /** @test */
    public function testThatSessionIsShared()
    {
        try {
            flash('Checking shared');

            $content = flash()->display();
            $this->assertContains('Checking shared', $content);
        } catch (FlashTemplateNotFoundException $e) {
            $this->assertFalse(true); // should fail the test
        }
    }

    /** @test */
    public function testItFlushesChanges()
    {
        try {
            flash('First one', 'success')->message('Other one', 'info')->display();
            flash('Third one', 'error')->display();

            $this->assertFalse(flash()->hasMessages());
        } catch (FlashTemplateNotFoundException $e) {
            $this->assertFalse(true); // should fail the test
        }
    }

    /** @test */
    public function testClearFunction()
    {
        try {
            flash('I\'ll never see this message', 'success');
            flash()->clear();

            $this->assertFalse(flash()->hasMessages());
        } catch (FlashTemplateNotFoundException $e) {
            $this->assertFalse(true); // should fail the test
        }
    }

    /** @test */
    public function testShortcuts()
    {
        try {
            flash()->error('Info message')->warning('Info message')->info('Info message')->success('Info message');

            $content = flash()->display();
            $this->assertContains('danger', $content);
            $this->assertContains('warning', $content);
            $this->assertContains('info', $content);
            $this->assertContains('success', $content);
        } catch (FlashTemplateNotFoundException $e) {
            $this->assertFalse(true); // should fail the test
        }
    }

    /** @test */
    public function testToString()
    {
        try {
            flash('Testing toString', 'success');
            $flash1 = new Flash();
            $this->assertContains('toString', (string)$flash1);

            flash('Testing toString', 'success');
            $flash2 = flash();
            $this->assertContains('toString', (string)$flash2);
        } catch (FlashTemplateNotFoundException $e) {
            $this->assertFalse(true); // should fail the test
        }
    }

    /** @test */
    public function testEmptyFunction()
    {
        try {
            flash('');
            $this->assertFalse(flash()->hasMessages());
        } catch (FlashTemplateNotFoundException $e) {
            $this->assertFalse(true); // should fail the test
        }
    }

    /** @test */
    public function testWorkWithArrays()
    {
        $errors = [
            'Invalid name',
            'Invalid email',
        ];

        try {
            flash($errors, 'error');

            $content = flash()->display();
            $this->assertContains('Invalid name', $content);
            $this->assertContains('Invalid email', $content);
        } catch (FlashTemplateNotFoundException $e) {
            $this->assertFalse(true); // should fail the test
        }
    }

    /** @test */
    public function testDefaultTemplate()
    {
        try {
            $template = TemplateFactory::create();
        } catch (FlashTemplateNotFoundException $e) {
            $this->assertFalse(true); // should fail the test
            return;
        }

        $prefix = $template->getPrefix();
        $postfix = $template->getPostfix();
        $template->setPrefix('');
        $template->setPostfix('');
        $template->setWrapper('<div class="flash flash-%s" role="alert">%s</div>');

        $flash = new Flash();

        $contentOriginal = $flash->info('Testing templates')->display();

        $flash->setTemplate($template);

        $content = $flash->info('Testing templates')->display();

        $this->assertEquals('', $prefix);
        $this->assertNotEquals($contentOriginal, $content);
        $this->assertContains('Testing templates', $content);
        $this->assertNotContains($postfix, $content);
    }

    /** @test */
    public function testClassWithTemplateConstructor()
    {
        try {
            $template = TemplateFactory::create(Templates::FOUNDATION);
            $flash = new Flash($template);

            $flash->info('Testing templates');

            $content = $flash->display();
            $this->assertContains('callout', $content);
        } catch (FlashTemplateNotFoundException $e) {
            $this->assertFalse(true); // should fail the test
        }
    }

    /** @test */
    public function testFunctionWithTemplateConstructor()
    {
        try {
            $template = TemplateFactory::create(Templates::FOUNDATION);

            flash('Testing templates', 'info', $template);

            $content = flash()->display();
            $this->assertContains('callout', $content);
        } catch (FlashTemplateNotFoundException $e) {
            $this->assertFalse(true); // should fail the test
        }
    }

    /** @test */
    public function testSetTemplateFunction()
    {
        try {
            $template = TemplateFactory::create(Templates::FOUNDATION);
            $flash = new Flash();

            $flash->info('Testing templates');

            $content = $flash->setTemplate($template)->display();
            $this->assertContains('callout', $content);
        } catch (FlashTemplateNotFoundException $e) {
            $this->assertFalse(true); // should fail the test
        }
    }

    /** @test */
    public function testGetTemplate()
    {
        try {
            $flash = new Flash();
            $flash->getTemplate()->setPrefix('AAAAAAAA')->setPostfix('BBBBBBBB');

            $flash->info('Testing templates');

            $content = $flash->display();
            $this->assertContains('AAAAAAAA', $content);
            $this->assertContains('BBBBBBBB', $content);
        } catch (FlashTemplateNotFoundException $e) {
            $this->assertFalse(true); // should fail the test
        }
    }

    /** @test */
    public function testStaticMethods()
    {
        try {
            Flash::setTemplate(TemplateFactory::create());

            Flash::info('Testing static');

            $content = Flash::display();
            $this->assertContains('Testing static', $content);
        } catch (FlashTemplateNotFoundException $e) {
            $this->assertFalse(true); // should fail the test
        }
    }

    /** @test */
    public function testCloneRestriction()
    {
        try {
            $flash = new Flash();
            $reflection = new ReflectionClass($flash);

            $this->assertFalse($reflection->isCloneable());
        } catch (FlashTemplateNotFoundException $e) {
            $this->assertFalse(true); // should fail the test
        } catch (ReflectionException $e) {
            $this->assertFalse(true); // should fail the test
        }
    }

    /** @test */
    public function testNotSerializable()
    {
        $flash = new Flash();

        try {
            serialize($flash);
        } catch (FlashSingletonException $e) {
            $this->assertContains('Serialization of Flash is not allowed!', $e->getMessage());
        }
    }

    /**
     * Need to be last - because spoils template.
     *
     * @test
     */
    public function testBadTemplate()
    {
        try {
            $template = new BadTemplate();
            $flash = new Flash();

            $flash->info('Testing templates');
            $flash->setTemplate($template)->display();
        } catch (FlashTemplateException $e) {
            $this->assertContains('Please, make sure you have prefix, postfix and wrapper defined!', $e->getMessage());
        }
    }
}
