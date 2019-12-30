<?php
/**
 *
 * Date: 2019/12/26 17:54
 */

namespace Alismall\Weather;

use Alismall\Weather\Exceptions\HttpException;
use Alismall\Weather\Exceptions\InvalidArgumentException;
use GuzzleHttp\Client;

/**
 * 按照单一职责原则，一个类只应该关心自己的逻辑，当出现问题的时候，如果不是当前类该处理的，我们就应该抛出而不是消化
 *
 * Class Weather
 * @package Alismall\Weather
 */
class Weather
{
    /**
     * 高德 APPKEY [https://lbs.amap.com/]
     *
     * @var string
     */
    protected $key;

    /**
     * 请求接口选项：超时时间等
     *
     * @var array
     */
    protected $guzzleOptions = [];


    public function __construct($key)
    {
        $this->key = $key;
    }

    public function getHttpClient()
    {
        return new Client($this->guzzleOptions);
    }

    /**
     * 其中我们设计了一个 $guzzleOptions 参数与方法 setGuzzleOptions，
     * 旨在用户可以自定义 guzzle 实例的参数，比如超时时间等
     *
     * @param array $options
     */
    public function setGuzzleOptions($options)
    {
        $this->guzzleOptions = $options;
    }

    /**
     * 获取天气
     *
     * @param $city
     * @param string $type
     * @param string $format
     * @return mixed|string
     */
    public function getWeather($city, $type = 'base', $format = 'json')
    {
        $url = 'https://restapi.amap.com/v3/weather/weatherInfo';

        if (!\in_array(\strtolower($format), ['xml', 'json'])) {
            throw new InvalidArgumentException('Invalid response format: ' . $format);
        }

        if (!\in_array(\strtolower($type), ['base', 'all'])) {
            throw new InvalidArgumentException('Invalid type value(base/all): ' . $type);
        }

        $query = array_filter([
            'key' => $this->key,
            'city' => $city,
            'output' => $format,
            'extensions' => $type,
        ]);

        try {
            $response = $this->getHttpClient()->get($url, [
                'query' => $query,
            ])->getBody()->getContents();

            return 'json' === $format ? \json_decode($response, true) : $response;
        } catch (\Exception $e) {
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }


        return 'json' === $format ? \json_decode($response, true) : $response;
    }

    public function getLiveWeather($city, $format = 'json')
    {
        return $this->getWeather($city, 'base', $format);
    }

    public function getForecastsWeather($city, $format = 'json')
    {
        return $this->getWeather($city, 'all', $format);
    }

}