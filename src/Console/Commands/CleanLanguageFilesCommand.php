<?php

namespace OPGG\LaravelEssentialsEntry\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class CleanLanguageFilesCommand extends Command
{
    /**
     * 명령어 시그니처
     *
     * @var string
     */
    protected $signature = 'lang:clean';

    /**
     * 명령어 설명
     *
     * @var string
     */
    protected $description = '기본 언어를 제외한 모든 언어 파일 삭제';

    /**
     * 명령어 실행
     *
     * @return int
     */
    public function handle()
    {
        // 기본 언어 가져오기
        $defaultLang = Config::get('essentials-entry.language.default');
        
        // 환경 변수에서 직접 가져오는 경우 처리
        if (empty($defaultLang)) {
            $defaultLang = env('APP_LOCALE', 'en');
        }
        
        $this->info("기본 언어: {$defaultLang}");
        
        // 언어 디렉토리 경로
        $langPath = base_path('lang');
        
        // 언어 디렉토리가 없으면 생성
        if (!File::exists($langPath)) {
            $this->warn("언어 디렉토리({$langPath})가 존재하지 않습니다.");
            return 0;
        }
        
        // 모든 언어 디렉토리 가져오기
        $langDirs = File::directories($langPath);
        
        if (empty($langDirs)) {
            $this->info("언어 디렉토리가 비어있습니다.");
            return 0;
        }
        
        $deletedFiles = 0;
        
        foreach ($langDirs as $langDir) {
            $langCode = basename($langDir);
            
            // 기본 언어인 경우 건너뛰기
            if ($langCode === $defaultLang) {
                $this->info("기본 언어({$langCode}) 디렉토리 유지");
                continue;
            }
            
            // 해당 언어 디렉토리의 모든 PHP 파일 가져오기
            $langFiles = File::glob("{$langDir}/*.php");
            
            if (empty($langFiles)) {
                $this->info("{$langCode} 디렉토리에 PHP 파일이 없음");
                continue;
            }
            
            // 해당 언어의 PHP 파일 삭제
            foreach ($langFiles as $file) {
                File::delete($file);
                $deletedFiles++;
                $this->line("삭제됨: {$file}");
            }
            
            $this->info("{$langCode} 디렉토리의 모든 PHP 파일 삭제 완료");
        }
        
        $this->info("총 {$deletedFiles}개 파일 삭제 완료");
        
        return 0;
    }
}
