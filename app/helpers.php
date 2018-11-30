<?php
use Illuminate\Support\Facades\Redis;

function ok($data)
{
    return [
        'status_code' => 200,
        'message' => 'ok',
        'data' => $data
    ];
}

function error($error, $code)
{
    static $_http_code = [
        400 => "Bad Request",           // 请求数据有问题
        401 => "Unauthorized",          // 未登录
        403 => "Forbidden",             // 登录但没有权限
        404 => "Not Found",             // 请求数据没找到
        422 => 'Unprocessable Entity',  // 无法处理输入的数据
        500 => 'Internal Server Error', // 服务器内部错误
    ];
    return [
        'status_code' => $code,
        'message' => $_http_code[$code],
        'errors' => $error
    ];
}

// 生成唯一订单编号
function getOrderSn()
{
    $sf = new SnowFlake(0, 0);
    return $sf->generateID();
}

class SnowFlake
{
    /**
     * Offset from Unix Epoch
     * Unix Epoch : January 1 1970 00:00:00 GMT
     * Epoch Offset : January 1 2000 00:00:00 GMT
     */
    const EPOCH_OFFSET = 1483200000000;
    const SIGN_BITS = 1;
    const TIMESTAMP_BITS = 41;
    const DATACENTER_BITS = 5;
    const MACHINE_ID_BITS = 5;
    const SEQUENCE_BITS = 12;

    /**
     * @var mixed
     */
    protected $datacenter_id;

    /**
     * @var mixed
     */
    protected $machine_id;

    /**
     * @var null|int
     */
    // protected $lastTimestamp = null;   需要保存到 Redis

    /**
     * @var int
     */
    // protected $sequence = 1;   需要保存到 Redis

    protected $signLeftShift = self::TIMESTAMP_BITS + self::DATACENTER_BITS + self::MACHINE_ID_BITS + self::SEQUENCE_BITS;
    protected $timestampLeftShift = self::DATACENTER_BITS + self::MACHINE_ID_BITS + self::SEQUENCE_BITS;
    protected $dataCenterLeftShift = self::MACHINE_ID_BITS + self::SEQUENCE_BITS;
    protected $machineLeftShift = self::SEQUENCE_BITS;
    protected $maxSequenceId = -1 ^ (-1 << self::SEQUENCE_BITS);
    protected $maxMachineId = -1 ^ (-1 << self::MACHINE_ID_BITS);
    protected $maxDataCenterId = -1 ^ (-1 << self::DATACENTER_BITS);

    /**
     * Constructor to set required paremeters
     *
     * @param mixed $dataCenter_id Unique ID for datacenter (if multiple locations are used)
     * @param mixed $machine_id Unique ID for machine (if multiple machines are used)
     * @throws \Exception
     */
    public function __construct($dataCenter_id, $machine_id)
    {
        if ($dataCenter_id > $this->maxDataCenterId) {
            throw new \Exception('dataCenter id should between 0 and ' . $this->maxDataCenterId);
        }
        if ($machine_id > $this->maxMachineId) {
            throw new \Exception('machine id should between 0 and ' . $this->maxMachineId);
        }
        $this->datacenter_id = $dataCenter_id;
        $this->machine_id = $machine_id;
    }

    /**
     * Generate an unique ID based on SnowFlake
     * @return string
     * @throws \Exception
     */
    public function generateID()
    {
        $sign = 0; // default 0
        $timestamp = $this->getUnixTimestamp();

        $lastTimeStamp = Redis::get('shop:order:lastTimeStamp');

        if ($timestamp < $lastTimeStamp) {
            throw new \Exception('"Clock moved backwards!');
        }
        if ($timestamp == $lastTimeStamp) { //与上次时间戳相等，需要生成序列号
            $sequence = Redis::incr('shop:order:sequence');
            if ($sequence == $this->maxSequenceId) { //如果序列号超限，则需要重新获取时间
                $timestamp = $this->getUnixTimestamp();
                while ($timestamp <= $lastTimeStamp) {
                    $timestamp = $this->getUnixTimestamp();
                }
                Redis::set('shop:order:sequence', 1);
                $sequence = 1;
            }
        } else {
            Redis::set('shop:order:sequence', 1);
            $sequence = 1;
        }
        Redis::set('shop:order:lastTimeStamp', $timestamp);
        $time = (int)($timestamp - self::EPOCH_OFFSET);
        $id = ($sign << $this->signLeftShift) | ($time << $this->timestampLeftShift) | ($this->datacenter_id << $this->dataCenterLeftShift) | ($this->machine_id << $this->machineLeftShift) | $sequence;
        return (string)$id;
    }

    /**
     * Get UNIX timestamp in microseconds
     *
     * @return int  Timestamp in microseconds
     */
    private function getUnixTimestamp()
    {
        return floor(microtime(true) * 1000);
    }
}