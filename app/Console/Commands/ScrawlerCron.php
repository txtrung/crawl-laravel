<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class ScrawlerCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrawler:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tell nodejs server to scapper data';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        info("Cron Job running at ". now());
        info("Cron Job running at ". auth()->user());

        $response = Http::post('http://localhost:3000/crawlerGo',[
            'token' => auth()->user()
        ]);
      
        $result = $response->json();
  
        info("Cron Job running result ". json_encode($result));

        return 0;
    }
}
