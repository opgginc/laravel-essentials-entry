<?php

namespace OPGG\LaravelEssentialsEntry\Schema;

use Illuminate\Support\Facades\Config;

class JsonLd
{
    /**
     * 스키마 데이터
     *
     * @var array
     */
    protected $data = [];
    
    /**
     * 스키마 타입
     *
     * @var string
     */
    protected $type;
    
    /**
     * 생성자
     *
     * @param  string  $type
     * @return void
     */
    public function __construct(string $type)
    {
        $this->type = $type;
        $this->data = [
            '@context' => 'https://schema.org',
            '@type' => $type,
        ];

        // 메타 태그 설정 적용
        $this->applyMetaTags();
    }
    
    /**
     * 메타 태그 설정을 적용합니다.
     *
     * @return void
     */
    protected function applyMetaTags(): void
    {
        $config = Config::get('essentials-entry.meta-tags');
        
        // 기본 메타 태그 설정
        if (!empty($config['defaults']['title'])) {
            $this->set('name', $config['defaults']['title']);
        }
        
        if (!empty($config['defaults']['description'])) {
            $this->set('description', $config['defaults']['description']);
        }
        
        // OpenGraph 설정
        if (!empty($config['og']['site_name'])) {
            $this->set('publisher', [
                '@type' => 'Organization',
                'name' => $config['og']['site_name'],
            ]);
        }
        
        if (!empty($config['og']['image'])) {
            $this->set('image', $config['og']['image']);
        }
        
        // Twitter 설정
        if (!empty($config['twitter']['site'])) {
            $this->set('sameAs', [
                'https://twitter.com/' . str_replace('@', '', $config['twitter']['site']),
            ]);
        }
    }
    
    /**
     * 제품 스키마 생성
     *
     * @return self
     */
    public static function product(): self
    {
        return new self('Product');
    }
    
    /**
     * 조직 스키마 생성
     *
     * @return self
     */
    public static function organization(): self
    {
        return new self('Organization');
    }
    
    /**
     * 웹사이트 스키마 생성
     *
     * @return self
     */
    public static function website(): self
    {
        return new self('WebSite');
    }
    
    /**
     * 기사 스키마 생성
     *
     * @return self
     */
    public static function article(): self
    {
        return new self('Article');
    }
    
    /**
     * 빵 부스러기 스키마 생성
     *
     * @return self
     */
    public static function breadcrumbList(): self
    {
        return new self('BreadcrumbList');
    }
    
    /**
     * FAQ 스키마 생성
     *
     * @return self
     */
    public static function faqPage(): self
    {
        return new self('FAQPage');
    }
    
    /**
     * 속성 설정
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return self
     */
    public function set(string $key, $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }
    
    /**
     * 이름 설정
     *
     * @param  string  $name
     * @return self
     */
    public function name(string $name): self
    {
        return $this->set('name', $name);
    }
    
    /**
     * 설명 설정
     *
     * @param  string  $description
     * @return self
     */
    public function description(string $description): self
    {
        return $this->set('description', $description);
    }
    
    /**
     * 이미지 설정
     *
     * @param  string  $image
     * @return self
     */
    public function image(string $image): self
    {
        return $this->set('image', $image);
    }
    
    /**
     * URL 설정
     *
     * @param  string  $url
     * @return self
     */
    public function url(string $url): self
    {
        return $this->set('url', $url);
    }
    
    /**
     * 가격 설정
     *
     * @param  float  $price
     * @return self
     */
    public function price(float $price): self
    {
        return $this->set('price', $price);
    }
    
    /**
     * 통화 설정
     *
     * @param  string  $currency
     * @return self
     */
    public function priceCurrency(string $currency): self
    {
        return $this->set('priceCurrency', $currency);
    }
    
    /**
     * 아이템 목록 설정
     *
     * @param  array  $items
     * @return self
     */
    public function itemListElement(array $items): self
    {
        return $this->set('itemListElement', $items);
    }
    
    /**
     * 질문 추가 (FAQ용)
     *
     * @param  string  $question
     * @param  string  $answer
     * @return self
     */
    public function addQuestion(string $question, string $answer): self
    {
        if (!isset($this->data['mainEntity'])) {
            $this->data['mainEntity'] = [];
        }
        
        $this->data['mainEntity'][] = [
            '@type' => 'Question',
            'name' => $question,
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $answer,
            ],
        ];
        
        return $this;
    }
    
    /**
     * JSON-LD 스크립트 태그 생성
     *
     * @return string
     */
    public function toScript(): string
    {
        $json = json_encode($this->data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        return '<script type="application/ld+json">' . $json . '</script>';
    }
    
    /**
     * JSON 문자열 반환
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * 배열 반환
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
