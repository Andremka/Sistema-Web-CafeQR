<?php

namespace App\Console\Commands;

use App\Mail\CafeQrMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ProbarCorreo extends Command
{
    protected $signature = 'correo:probar {correo : Correo que recibirá la prueba}';

    protected $description = 'Envía un correo de prueba para comprobar la configuración MAIL_* del .env';

    public function handle(): int
    {
        $this->info('Enviando con: '.config('mail.default').' / '.config('mail.mailers.smtp.host').':'.config('mail.mailers.smtp.port'));

        if (in_array(config('mail.default'), ['log', 'array'], true)) {
            $this->warn('MAIL_MAILER está en "'.config('mail.default').'": el correo NO sale a internet, solo se anota en storage/logs/laravel.log.');
            $this->warn('Para que lleguen de verdad, configura MAIL_MAILER=smtp y los datos de Gmail en el archivo .env');
        }

        try {
            Mail::to($this->argument('correo'))->send(new CafeQrMail(
                'Prueba de correo · CafeQR',
                'emails.prueba',
                ['mensaje' => 'Si lees esto, el envío de correos de CafeQR funciona correctamente.']
            ));
        } catch (\Throwable $e) {
            $this->error('No se pudo enviar: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Listo. Revisa la bandeja de entrada (y la carpeta de spam) de '.$this->argument('correo'));

        return self::SUCCESS;
    }
}
