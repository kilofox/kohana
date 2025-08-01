<?php

/**
 * Tests the Encrypt_Openssl class.
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.encrypt
 * @group kohana.core.encrypt.openssl
 *
 * @package     Kohana
 * @category    Tests
 * @author      Kohana Team
 * @author      Samuel Demirdjian <sam@enov.ws>
 * @author      Loong <loong2460@gmail.com>
 * @copyright   (c) 2014 Kohana Team
 * @copyright   (c) 2018 Kohana Group
 * @license     https://kohana.top/license
 */
class Kohana_Encrypt_OpensslTest extends Unittest_TestCase
{
    /**
     * Provider for test_encode.
     *
     * AES Multiblock Message Test (MMT) Sample Vectors - Known Answer Test (KAT).
     * @link https://csrc.nist.gov/projects/cryptographic-algorithm-validation-program NIST - Cryptographic Algorithm Validation Program
     * @link https://csrc.nist.gov/csrc/media/projects/cryptographic-algorithm-validation-program/documents/aes/aesmmt.zip file used CBCMMT128.rsp
     *
     * @return  array   Array of $method, $key, $iv, $txtPlain, $txtEncoded.
     */
    public function providerEncode()
    {
        return [
            [
                // method
                'AES-128-CBC',
                // key
                pack('H*', '1f8e4973953f3fb0bd6b16662e9a3c17'),
                // iv
                pack('H*', '2fe2b333ceda8f98f4a99b40d2cd34a8'),
                // txtPlain
                pack('H*', '45cf12964fc824ab76616ae2f4bf0822'),
                // txtEncoded
                pack('H*', '0f61c4d44c5147c03c195ad7e2cc12b2'),
            ],
            [
                // method
                'AES-128-CBC',
                // key
                pack('H*', '0700d603a1c514e46b6191ba430a3a0c'),
                // iv
                pack('H*', 'aad1583cd91365e3bb2f0c3430d065bb'),
                // txtPlain
                pack('H*', '068b25c7bfb1f8bdd4cfc908f69dffc5ddc726a197f0e5f720f730393279be91'),
                // txtEncoded
                pack('H*', 'c4dc61d9725967a3020104a9738f23868527ce839aab1752fd8bdb95a82c4d00'),
            ],
            [
                // method
                'AES-128-CBC',
                // key
                pack('H*', '3348aa51e9a45c2dbe33ccc47f96e8de'),
                // iv
                pack('H*', '19153c673160df2b1d38c28060e59b96'),
                // txtPlain
                pack('H*', '9b7cee827a26575afdbb7c7a329f887238052e3601a7917456ba61251c214763d5e1847a6ad5d54127a399ab07ee3599'),
                // txtEncoded
                pack('H*', 'd5aed6c9622ec451a15db12819952b6752501cf05cdbf8cda34a457726ded97818e1f127a28d72db5652749f0c6afee5'),
            ],
            [
                // method
                'AES-128-CBC',
                // key
                pack('H*', 'b7f3c9576e12dd0db63e8f8fac2b9a39'),
                // iv
                pack('H*', 'c80f095d8bb1a060699f7c19974a1aa0'),
                // txtPlain
                pack('H*', '9ac19954ce1319b354d3220460f71c1e373f1cd336240881160cfde46ebfed2e791e8d5a1a136ebd1dc469dec00c4187722b841cdabcb22c1be8a14657da200e'),
                // txtEncoded
                pack('H*', '19b9609772c63f338608bf6eb52ca10be65097f89c1e0905c42401fd47791ae2c5440b2d473116ca78bd9ff2fb6015cfd316524eae7dcb95ae738ebeae84a467'),
            ],
            [
                // method
                'AES-128-CBC',
                // key
                pack('H*', 'b6f9afbfe5a1562bba1368fc72ac9d9c'),
                // iv
                pack('H*', '3f9d5ebe250ee7ce384b0d00ee849322'),
                // txtPlain
                pack('H*', 'db397ec22718dbffb9c9d13de0efcd4611bf792be4fce0dc5f25d4f577ed8cdbd4eb9208d593dda3d4653954ab64f05676caa3ce9bfa795b08b67ceebc923fdc89a8c431188e9e482d8553982cf304d1'),
                // txtEncoded
                pack('H*', '10ea27b19e16b93af169c4a88e06e35c99d8b420980b058e34b4b8f132b13766f72728202b089f428fecdb41c79f8aa0d0ef68f5786481cca29e2126f69bc14160f1ae2187878ba5c49cf3961e1b7ee9'),
            ],
            [
                // method
                'AES-128-CBC',
                // key
                pack('H*', 'bbe7b7ba07124ff1ae7c3416fe8b465e'),
                // iv
                pack('H*', '7f65b5ee3630bed6b84202d97fb97a1e'),
                // txtPlain
                pack('H*', '2aad0c2c4306568bad7447460fd3dac054346d26feddbc9abd9110914011b4794be2a9a00a519a51a5b5124014f4ed2735480db21b434e99a911bb0b60fe0253763725b628d5739a5117b7ee3aefafc5b4c1bf446467e7bf5f78f31ff7caf187'),
                // txtEncoded
                pack('H*', '3b8611bfc4973c5cd8e982b073b33184cd26110159172e44988eb5ff5661a1e16fad67258fcbfee55469267a12dc374893b4e3533d36f5634c3095583596f135aa8cd1138dc898bc5651ee35a92ebf89ab6aeb5366653bc60a70e0074fc11efe'),
            ],
            [
                // method
                'AES-128-CBC',
                // key
                pack('H*', '89a553730433f7e6d67d16d373bd5360'),
                // iv
                pack('H*', 'f724558db3433a523f4e51a5bea70497'),
                // txtPlain
                pack('H*', '807bc4ea684eedcfdcca30180680b0f1ae2814f35f36d053c5aea6595a386c1442770f4d7297d8b91825ee7237241da8925dd594ccf676aecd46ca2068e8d37a3a0ec8a7d5185a201e663b5ff36ae197110188a23503763b8218826d23ced74b31e9f6e2d7fbfa6cb43420c7807a8625'),
                // txtEncoded
                pack('H*', '406af1429a478c3d07e555c5287a60500d37fc39b68e5bbb9bafd6ddb223828561d6171a308d5b1a4551e8a5e7d572918d25c968d3871848d2f16635caa9847f38590b1df58ab5efb985f2c66cfaf86f61b3f9c0afad6c963c49cee9b8bc81a2ddb06c967f325515a4849eec37ce721a'),
            ],
            [
                // method
                'AES-128-CBC',
                // key
                pack('H*', 'c491ca31f91708458e29a925ec558d78'),
                // iv
                pack('H*', '9ef934946e5cd0ae97bd58532cb49381'),
                // txtPlain
                pack('H*', 'cb6a787e0dec56f9a165957f81af336ca6b40785d9e94093c6190e5152649f882e874d79ac5e167bd2a74ce5ae088d2ee854f6539e0a94796b1e1bd4c9fcdbc79acbef4d01eeb89776d18af71ae2a4fc47dd66df6c4dbe1d1850e466549a47b636bcc7c2b3a62495b56bb67b6d455f1eebd9bfefecbca6c7f335cfce9b45cb9d'),
                // txtEncoded
                pack('H*', '7b2931f5855f717145e00f152a9f4794359b1ffcb3e55f594e33098b51c23a6c74a06c1d94fded7fd2ae42c7db7acaef5844cb33aeddc6852585ed0020a6699d2cb53809cefd169148ce42292afab063443978306c582c18b9ce0da3d084ce4d3c482cfd8fcf1a85084e89fb88b40a084d5e972466d07666126fb761f84078f2'),
            ],
            [
                // method
                'AES-128-CBC',
                // key
                pack('H*', 'f6e87d71b0104d6eb06a68dc6a71f498'),
                // iv
                pack('H*', '1c245f26195b76ebebc2edcac412a2f8'),
                // txtPlain
                pack('H*', 'f82bef3c73a6f7f80db285726d691db6bf55eec25a859d3ba0e0445f26b9bb3b16a3161ed1866e4dd8f2e5f8ecb4e46d74a7a78c20cdfc7bcc9e479ba7a0caba9438238ad0c01651d5d98de37f03ddce6e6b4bd4ab03cf9e8ed818aedfa1cf963b932067b97d776dce1087196e7e913f7448e38244509f0caf36bd8217e15336d35c149fd4e41707893fdb84014f8729'),
                // txtEncoded
                pack('H*', 'b09512f3eff9ed0d85890983a73dadbb7c3678d52581be64a8a8fc586f490f2521297a478a0598040ebd0f5509fafb0969f9d9e600eaef33b1b93eed99687b167f89a5065aac439ce46f3b8d22d30865e64e45ef8cd30b6984353a844a11c8cd60dba0e8866b3ee30d24b3fa8a643b328353e06010fa8273c8fd54ef0a2b6930e5520aae5cd5902f9b86a33592ca4365'),
            ],
            [
                // method
                'AES-128-CBC',
                // key
                pack('H*', '2c14413751c31e2730570ba3361c786b'),
                // iv
                pack('H*', '1dbbeb2f19abb448af849796244a19d7'),
                // txtPlain
                pack('H*', '40d930f9a05334d9816fe204999c3f82a03f6a0457a8c475c94553d1d116693adc618049f0a769a2eed6a6cb14c0143ec5cccdbc8dec4ce560cfd206225709326d4de7948e54d603d01b12d7fed752fb23f1aa4494fbb00130e9ded4e77e37c079042d828040c325b1a5efd15fc842e44014ca4374bf38f3c3fc3ee327733b0c8aee1abcd055772f18dc04603f7b2c1ea69ff662361f2be0a171bbdcea1e5d3f'),
                // txtEncoded
                pack('H*', '6be8a12800455a320538853e0cba31bd2d80ea0c85164a4c5c261ae485417d93effe2ebc0d0a0b51d6ea18633d210cf63c0c4ddbc27607f2e81ed9113191ef86d56f3b99be6c415a4150299fb846ce7160b40b63baf1179d19275a2e83698376d28b92548c68e06e6d994e2c1501ed297014e702cdefee2f656447706009614d801de1caaf73f8b7fa56cf1ba94b631933bbe577624380850f117435a0355b2b'),
            ],
        ];
    }

    /**
     * Test encode.
     *
     * @param   string  $method     Encryption cipher.
     * @param   string  $key        Encryption key.
     * @param   string  $iv         Initialization vector.
     * @param   string  $txtPlain   Plain text to be encrypted.
     * @param   string  $txtEncoded Known ecrypted text.
     *
     * @dataProvider providerEncode
     * @covers Encrypt_Openssl::encode
     */
    public function testEncode($method, $key, $iv, $txtPlain, $txtEncoded)
    {
        // Initialize.
        $e = $this->getMockBuilder('Encrypt_Openssl')
            ->enableOriginalConstructor()
            ->setConstructorArgs(['name' => 'openssl', 'config' => ['driver' => 'openssl', 'method' => $method, 'key' => $key, 'iv' => $iv]])
            ->setMethods()
            ->getMock();

        // Prepare data.
        $expected = base64_encode($iv . $txtEncoded);
        $actual = $e->encode($txtPlain);

        // Assert.
        $this->assertSame($expected, $actual);
    }

    /**
     * Provider for test_decode.
     *
     * AES Multiblock Message Test (MMT) Sample Vectors - Known Answer Test (KAT).
     * @link https://csrc.nist.gov/projects/cryptographic-algorithm-validation-program NIST - Cryptographic Algorithm Validation Program
     * @link https://csrc.nist.gov/csrc/media/projects/cryptographic-algorithm-validation-program/documents/aes/aesmmt.zip file used CBCMMT128.rsp
     *
     * @return  array   Array of $method, $key, $iv, $txtEncoded, $txtPlain.
     */
    public function providerDecode()
    {
        return [
            [
                // method
                'AES-128-CBC',
                // key
                pack('H*', '6a7082cf8cda13eff48c8158dda206ae'),
                // iv
                pack('H*', 'bd4172934078c2011cb1f31cffaf486e'),
                // txtEncoded
                pack('H*', 'f8eb31b31e374e960030cd1cadb0ef0c'),
                // txtPlain
                pack('H*', '940bc76d61e2c49dddd5df7f37fcf105'),
            ],
            [
                // method
                'AES-128-CBC',
                // key
                pack('H*', '625eefa18a4756454e218d8bfed56e36'),
                // iv
                pack('H*', '73d9d0e27c2ec568fbc11f6a0998d7c8'),
                // txtEncoded
                pack('H*', '5d6fed86f0c4fe59a078d6361a142812514b295dc62ff5d608a42ea37614e6a1'),
                // txtPlain
                pack('H*', '360dc1896ce601dfb2a949250067aad96737847a4580ede2654a329b842fe81e'),
            ],
            [
                // method
                'AES-128-CBC',
                // key
                pack('H*', 'fd6e0b954ae2e3b723d6c9fcae6ab09b'),
                // iv
                pack('H*', 'f08b65c9f4dd950039941da2e8058c4e'),
                // txtEncoded
                pack('H*', 'e29e3114c8000eb484395b256b1b3267894f290d3999819ff35da03e6463c186c4d7ebb964941f1986a2d69572fcaba8'),
                // txtPlain
                pack('H*', 'a206385945b21f812a9475f47fddbb7fbdda958a8d14c0dbcdaec36e8b28f1f6ececa1ceae4ce17721d162c1d42a66c1'),
            ],
            [
                // method
                'AES-128-CBC',
                // key
                pack('H*', '7b1ab9144b0239315cd5eec6c75663bd'),
                // iv
                pack('H*', '0b1e74f45c17ff304d99c059ce5cde09'),
                // txtEncoded
                pack('H*', 'd3f89b71e033070f9d7516a6cb4ea5ef51d6fb63d4f0fea089d0a60e47bbb3c2e10e9ba3b282c7cb79aefe3068ce228377c21a58fe5a0f8883d0dbd3d096beca'),
                // txtPlain
                pack('H*', 'b968aeb199ad6b3c8e01f26c2edad444538c78bfa36ed68ca76123b8cdce615a01f6112bb80bfc3f17490578fb1f909a52e162637b062db04efee291a1f1af60'),
            ],
            [
                // method
                'AES-128-CBC',
                // key
                pack('H*', '36466b6bd25ea3857ea42f0cac1919b1'),
                // iv
                pack('H*', '7186fb6bdfa98a16189544b228f3bcd3'),
                // txtEncoded
                pack('H*', '9ed957bd9bc52bba76f68cfbcde52157a8ca4f71ac050a3d92bdebbfd7c78316b4c9f0ba509fad0235fdafe90056ad115dfdbf08338b2acb1c807a88182dd2a882d1810d4302d598454e34ef2b23687d'),
                // txtPlain
                pack('H*', '999983467c47bb1d66d7327ab5c58f61ddb09b93bd2460cb78cbc12b5fa1ea0c5f759ccc5e478697687012ff4673f6e61eecaeda0ccad2d674d3098c7d17f887b62b56f56b03b4d055bf3a4460e83efa'),
            ],
            [
                // method
                'AES-128-CBC',
                // key
                pack('H*', '89373ee6e28397640d5082eed4123239'),
                // iv
                pack('H*', '1a74d7c859672c804b82472f7e6d3c6b'),
                // txtEncoded
                pack('H*', '1bcba44ddff503db7c8c2ec4c4eea0e827957740cce125c1e11769842fa97e25f1b89269e6d77923a512a358312f4ba1cd33f2d111280cd83e1ef9e7cf7036d55048d5c273652afa611cc81b4e9dac7b5078b7c4716062e1032ead1e3329588a'),
                // txtPlain
                pack('H*', '45efd00daa4cdc8273ef785cae9e944a7664a2391e1e2c449f475acec0124bbc22944331678617408a1702917971f4654310ffb9229bec6173715ae512d37f93aaa6abf009f7e30d65669d1db0366b5bce4c7b00f871014f5753744a1878dc57'),
            ],
            [
                // method
                'AES-128-CBC',
                // key
                pack('H*', 'bab0cceddc0abd63e3f82e9fbff7b8aa'),
                // iv
                pack('H*', '68b9140f300490c5c942f66e777eb806'),
                // txtEncoded
                pack('H*', 'c65b94b1f291fa9f0600f22c3c0432c895ad5d177bcccc9ea44e8ec339c9adf43855b326179d6d81aa36ef59462fd86127e9d81b0f286f93306bf74d4c79e47c1b3d4b74edd3a16290e3c63b742e41f20d66ceee794316bb63d3bd002712a1b136ba6185bd5c1dab81b07db90d2af5e5'),
                // txtPlain
                pack('H*', 'c5585ff215bbb73ba5393440852fb199436de0d15e55c631f877670aa3eda9f672eb1f876f09544e63558436b8928000db2f02a5ad90f95b05ac4cf49e198e617e7678480fdf0efacc6aae691271e6cdd3541ebf719a1ccaedb24e2f80f92455dd5910cb5086b0960a3942ec182dcbd7'),
            ],
            [
                // method
                'AES-128-CBC',
                // key
                pack('H*', '9c702898efa44557b29ed283f5bc0293'),
                // iv
                pack('H*', 'cec6e1b82e8b2a591a9fa5ff1cf5cc51'),
                // txtEncoded
                pack('H*', 'ba9f646755dacc22911f51d7de2f7e7cb0bc0b75257ea44fe883edb055c7c28ede04c3a0adcb10128ad4517d0093fa16bb0bcd2635e7a0ba92c7609bc8d8568002a7a983473724d256513aa7d51b477aabec1975ab5faf2872a6407e922180eff02f1ef86a4591c8bd3d143da6f0ef0e4806f94ace0d5b0151c99640fccbc843'),
                // txtPlain
                pack('H*', '1d1f8d81bdc3e2c7cb057f408e6450000c5aaed3260ff1e87fbb6f324df6887ffd8f78d7e2a04c9ed9deda9d64482d2b002f4a2b78d8b4f691875c8295d4a64b22257ceaf713ed2f4b92530d7ad7151d629acda882b4829577a43990b0948c1149c22fe4273656d1b08833930e8b06709a94579a78fc220f7057bbc1fa9f6563'),
            ],
            [
                // method
                'AES-128-CBC',
                // key
                pack('H*', '5674636dbdb38f705f0b08c372ef4785'),
                // iv
                pack('H*', '3f20ce0509b57420d53b6be4d0b7f0a9'),
                // txtEncoded
                pack('H*', '198351f453103face6655666fe90bdbd9630e3733b2d66c013a634e91f2bf015bd2d975d71b26322e44defa32d4e9dce50363557046ece08ba38f258dae5fd3e5049c647476c81e73482e40c171d89f9fea29452caf995733589b0061464fbd5dabe27dc5ea463a3deeb7dcb43664ae6a65c498c143883ab8e83b51e5410b181647602443dc3cfffe86f0205398fa83c'),
                // txtPlain
                pack('H*', '6d40fd2f908f48ce19241b6b278b1b1676dffd4a97ce9f8a1574c33bc59237deb536bee376fd6c381e6987700e39283aa111cf1a59f26fae6fb6700bf012646a2ab80239bf5e1632329043aa87d7911978b36523a2bc0bed9a9737ccf7a00baa2f3822b4e9e742e168e7069290705fed2eb63aa044b78f97dd33a8d6b24741ec1fd8c8db79d93b884e762dba0f406961'),
            ],
            [
                // method
                'AES-128-CBC',
                // key
                pack('H*', '97a1025529b9925e25bbe78770ca2f99'),
                // iv
                pack('H*', 'd4b4eab92aa9637e87d366384ed6915c'),
                // txtEncoded
                pack('H*', '22cdc3306fcd4d31ccd32720cbb61bad28d855670657c48c7b88c31f4fa1f93c01b57da90be63ead67d6a325525e6ed45083e6fb70a53529d1fa0f55653b942af59d78a2660361d63a7290155ac5c43312a25b235dacbbc863faf00940c99624076dfa44068e7c554c9038176953e571751dfc0954d41d113771b06466b1c8d13e0d4cb675ed58d1a619e1540970983781dc11d2dd8525ab5745958d615defda'),
                // txtPlain
                pack('H*', 'e8b89150d8438bf5b17449d6ed26bd72127e10e4aa57cad85283e8359e089208e84921649f5b60ea21f7867cbc9620560c4c6238db021216db453c9943f1f1a60546173daef2557c3cdd855031b353d4bf176f28439e48785c37d38f270aa4a6faad2baabcb0c0b2d1dd5322937498ce803ba1148440a52e227ddba4872fe4d81d2d76a939d24755adb8a7b8452ceed2d179e1a5848f316f5c016300a390bfa7'),
            ],
        ];
    }

    /**
     * Test decode.
     *
     * @param   string  $method     Encryption cipher.
     * @param   string  $key        Encryption key.
     * @param   string  $iv         Initialization vector.
     * @param   string  $txtEncoded Encrypted text.
     * @param   string  $txtPlain   Known plain text that is decrypted.
     *
     * @dataProvider providerDecode
     * @covers Encrypt_Openssl::decode
     */
    public function testDecode($method, $key, $iv, $txtEncoded, $txtPlain)
    {
        // Initialize.
        $e = $this->getMockBuilder('Encrypt_Openssl')
            ->enableOriginalConstructor()
            ->setConstructorArgs(['name' => 'openssl', 'config' => ['driver' => 'openssl', 'method' => $method, 'key' => $key]])
            ->setMethods()
            ->getMock();

        // Prepare data.
        $expected = $txtPlain;
        $actual = $e->decode(base64_encode($iv . $txtEncoded));

        // Assert.
        $this->assertSame($expected, $actual);
    }

    /**
     * Provider for test_encode_decode, test_consecutive_encode_different_results.
     *
     * @return  array   Array of $key, $method, $txtPlain.
     */
    public function providerEncodeDecode()
    {
        return [
            [
                // key
                'Some super secret key',
                // method
                'AES-128-OFB',
                // txtPlain
                'The quick brown fox jumps over the lazy dog',
            ],
            [
                // key
                'De finibus bonorum et malorum',
                // method
                'AES-128-OFB',
                // txtPlain
                'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            ],
        ];
    }

    /**
     * Test encode/decode.
     *
     * @param   string  $key        Encryption key.
     * @param   string  $method     Encryption cipher.
     * @param   string  $txtPlain   Plain text to encode and then decode back.
     *
     * @dataProvider providerEncodeDecode
     * @covers Encrypt_Openssl::encode
     * @covers Encrypt_Openssl::decode
     */
    public function testEncodeDecode($key, $method, $txtPlain)
    {
        // Initialize.
        $e = $this->getMockBuilder('Encrypt_Openssl')
            ->enableOriginalConstructor()
            ->setConstructorArgs(['name' => 'openssl', 'config' => ['driver' => 'openssl', 'method' => $method, 'key' => $key]])
            ->setMethods()
            ->getMock();

        // Encode.
        $txtEncoded = $e->encode($txtPlain);

        // Prepare data.
        $expected = $txtPlain;
        $actual = $e->decode($txtEncoded);

        // Assert.
        $this->assertSame($expected, $actual);
    }

    /**
     * Provider for test_decode_invalid_data.
     *
     * @return  array   Array of $key, $method, $txtInvalidEncoded.
     */
    public function providerDecodeInvalidDate()
    {
        return [
            [
                // key
                'Some super secret key',
                // method
                'AES-128-CBC',
                // txtInvalidEncoded
                '.:This data is not a valid base 64 string:.',
            ],
            [
                // key
                'Some super secret key',
                // method
                'AES-128-CBC',
                // txtInvalidEncoded
                base64_encode('too short'),
            ],
        ];
    }

    /**
     * Tests for decode when the string is not valid base64, or is too short to
     * contain a valid IV.
     *
     * @param   string  $key                    Encryption key.
     * @param   string  $method                 Encryption cipher.
     * @param   string  $txtInvalidEncoded      Invalid encoded text.
     *
     * @dataProvider providerDecodeInvalidDate
     */
    public function testDecodeInvalidData($key, $method, $txtInvalidEncoded)
    {
        // Initialize.
        $e = $this->getMockBuilder('Encrypt_Openssl')
            ->enableOriginalConstructor()
            ->setConstructorArgs(['name' => 'openssl', 'config' => ['driver' => 'openssl', 'cipher' => $method, 'key' => $key]])
            ->setMethods()
            ->getMock();

        // Assert.
        $this->AssertFalse($e->decode($txtInvalidEncoded));
    }

    /**
     * Validate consecutive encode produce different results.
     *
     * @param   string  $key        Encryption key.
     * @param   string  $method     Encryption cipher.
     * @param   string  $txtPlain   Plain text to encode and then decode back.
     *
     * @dataProvider providerEncodeDecode
     * @covers Encrypt_Openssl::encode
     */
    public function testConsecutiveEncodeProduceDifferentResults($key, $method, $txtPlain)
    {
        // Initialize, encode twice.
        $e = $this->getMockBuilder('Encrypt_Openssl')
            ->enableOriginalConstructor()
            ->setConstructorArgs(['name' => 'openssl', 'config' => ['driver' => 'openssl', 'cipher' => $method, 'key' => $key]])
            ->setMethods()
            ->getMock();

        $txtEncodedFirst = $e->encode($txtPlain);
        $txtEncodedSecond = $e->encode($txtPlain);

        // Assert.
        $this->assertNotEquals($txtEncodedFirst, $txtEncodedSecond);
    }

    /**
     * Validate the instance of Encrypt_Openssl throw an exception when no key provided.
     */
    public function testInstanceThrowExceptionWhenNoKeyProvided()
    {
        $this->expectException(Kohana_Exception::class);
        $this->expectExceptionMessage('No encryption key is defined in the encryption configuration group: default');

        Encrypt::instance('default', ['driver' => 'openssl']);
    }

    /**
     * Provider for test_instance_returns_singleton.
     *
     * @return  array   Array of $instanceName, $configArray.
     */
    public function providerInstanceReturnsSingleton()
    {
        return [
            [
                'default',
                [
                    'driver' => 'openssl',
                    'key' => 'trwQwVXX96TIJoKxyBHB9AJkwAOHixuV1ENZmIWyanI0j1zNgSVvqywy044Agaj',
                ]
            ],
            [
                'blowfish',
                [
                    'driver' => 'openssl',
                    'key' => '7bZJJkmNrelj5NaKoY6h6rMSRSmeUlJuTeOd5HHka5XknyMX4uGSfeVolTz4IYy',
                    'method' => 'BF-ECB'
                ]
            ],
            [
                'tripledes',
                [
                    'driver' => 'openssl',
                    'key' => 'a9hcSLRvA3LkFc7EJgxXIKQuz1ec91J7P6WNq1IaxMZp4CTj5m31gZLARLxI1jD',
                    'method' => 'DES-EDE3-CBC'
                ]
            ],
        ];
    }

    /**
     * Test to multiple calls to the instance() method returns same instance.
     * Also test if the instances are appropriately configured.
     *
     * @param string $instanceName Instance name.
     * @param array $configArray Array of config variables missing from config.
     *
     * @throws Kohana_Exception
     * @throws ReflectionException
     * @dataProvider providerInstanceReturnsSingleton
     */
    public function testInstanceReturnsSingleton($instanceName, array $configArray)
    {
        // Load config.
        $config = Kohana::$config->load('encrypt');
        // If instance name is null the config group should be the default.
        $configGroup = $instanceName ?: Encrypt::$default;
        // If config group does not exist, create one.
        if (!array_key_exists($configGroup, $config)) {
            $config[$configGroup] = [];
        }
        // Fill in the missing config variables.
        $config[$configGroup] = $config[$configGroup] + $configArray;

        // Call instance twice.
        $e = Encrypt::instance($instanceName);
        $e2 = Encrypt::instance($instanceName);

        // Assert instances.
        $this->assertInstanceOf('Encrypt_Openssl', $e);
        $this->assertInstanceOf('Encrypt_Openssl', $e2);
        $this->assertSame($e, $e2);

        // Test if instances are well configured.
        $expectedMethod = $config[$configGroup]['method'];
        $expectedKey = $config[$configGroup]['key'];

        // Assert.
        $this->assertSameProtectedProperty($expectedMethod, $e, 'method');
        $this->assertSameProtectedProperty($expectedKey, $e, 'key');
    }

    /**
     * Helper method to test for private/protected properties.
     *
     * @param mixed $expect Expected value.
     * @param mixed $object Object that holds the private/protected property.
     * @param string $name The name of the private/protected property.
     * @throws ReflectionException
     */
    protected function assertSameProtectedProperty($expect, $object, $name)
    {
        $refl = new ReflectionClass($object);
        $property = $refl->getProperty($name);
        $property->setAccessible(true);
        $this->assertSame($expect, $property->getValue($object));
    }

}
