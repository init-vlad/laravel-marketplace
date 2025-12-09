<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Logs\Exporter\ConsoleExporterFactory;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogRecordProcessor;
use OpenTelemetry\SDK\Resource\ResourceInfo;

use OpenTelemetry\API\Signals;
use OpenTelemetry\Contrib\Grpc\GrpcTransportFactory;
use OpenTelemetry\Contrib\Otlp\OtlpUtil;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->traceExample();
        // $this->logExample();
    }

    public function traceExample()
    {
        $transport = (new GrpcTransportFactory())->create('http://collector:4317' . OtlpUtil::method(Signals::TRACE));
        $exporter = new SpanExporter($transport);
        echo 'Starting OTLP GRPC example';

        $tracerProvider =  new TracerProvider(
            new SimpleSpanProcessor(
                $exporter
            )
        );
        $tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');

        $root = $span = $tracer->spanBuilder('root')->startSpan();
        $scope = $span->activate();

        for ($i = 0; $i < 3; $i++) {
            // start a span, register some events
            $span = $tracer->spanBuilder('loop-' . $i)->startSpan();

            $span->setAttribute('remote_ip', '1.2.3.4')
                ->setAttribute('country', 'USA');

            $span->addEvent('found_login' . $i, [
                'id' => $i,
                'username' => 'otuser' . $i,
            ]);
            $span->addEvent('generated_session', [
                'id' => md5((string) microtime(true)),
            ]);

            $span->end();
        }
        $root->end();
        $scope->detach();
        echo PHP_EOL . 'OTLP GRPC example complete!  ';

        echo PHP_EOL;
        $tracerProvider->shutdown();
    }

    public function logExample()
    {
        $loggerProvider = LoggerProvider::builder()
            ->addLogRecordProcessor(
                new SimpleLogRecordProcessor(
                    (new ConsoleExporterFactory())->create()
                )
            )
            ->setResource(ResourceInfo::create(Attributes::create(['foo' => 'bar'])))
            ->build();

        $logger = $loggerProvider->getLogger('demo', '1.0', 'http://schema.url', ['foo' => 'bar']);

        $record = (new LogRecord(['foo' => 'bar', 'baz' => 'bat', 'msg' => 'hello world']))
            ->setSeverityText('INFO')
            ->setSeverityNumber(9);

        /**
         * Note that Loggers should only be used directly by a log appender.
         * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.32.0/specification/logs/bridge-api.md#logs-bridge-api
         */
        $logger->emit($record);
        $loggerProvider->shutdown();
    }
}
