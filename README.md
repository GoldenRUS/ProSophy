ProSophy
========
##Структура файлов и папок
//дописать
--------------
#За что у нас бьют по рукам
* За плохой код (ниже читайте как сделать его хорошим)
* За перезапись своим комитом чужого
* За те правки, которые ломают предыдущий функционал (даже если СРОЧНОНУЖНОБЫСТРОБЛЯТЬ)
* За повторное создание велосипедов (и так уже много сделано)

--------------
##Рекомендации по хорошему коду
###Стиль кода
Стилей кода бывает многомногомного, но у нас в проекте используется noname стиль, обеспечивающий хорошую читабельность и надёжность кода.
###Примеры:
**Пример плохого if'а**

    if (isset($_POST['senderWareHouseOn']))
    //$array['sender_address'] = $_POST['senderWarehouse'];
        $array['sender_address'] = novaPoshtaAPI::senderWarehouse();
    else
        $array['sender_address'] = $_POST['senderAddress'];

**Пример хорошего if'а**

    if(isset($_POST['senderWareHouseOn'])){
        $array['sender_address'] = novaPoshtaAPI::senderWarehouse();
    }else{
        $array['sender_address'] = $_POST['senderAddress'];
    }

**Пример замечательного if'a**

    $array['sender_address'] = (isset($_POST['senderWareHouseOn']) ? novaPoshtaAPI::senderWarehouse() : $_POST['senderAddress']);

**Применчание**: стиль "замечательного if'а" следует применять только в случаях, если нужно задать единую переменную.

Если вы хотите выделить свой участок кода в каком-либо файле, не стоит этот кусок со всех сторон описывать коментариями: достаточно задать перед методом в классе (в котором метод обьявлен, а не в котором вы его вызываете) коментарий вида:

    /**
    * Что делает функция
    *
    * @param входные параметры (если есть) и их вид
    * @return что возвращает
    * @author имя автора
	*/

**Пример выделения своей функции среди других**

    /**
    * Возвращает сумму заказа
    *
    * @param    int $order
    * @return   orderSumm
    * @author   Nikolai Gilko
    */
    function getSummByOrder($order){
    ...
    }

Таким обьявлением все участники проекта смогут увидеть всю информацию про вашу функцию, что упростит для всех понимание кода и тд. Вы можете использовать любые переменные, понятные для PHPDoc

**Применчание**: выделение функций и их документирование на данный момент не является обязательным, но никто не исключает что это потребуется в будущем.

**Переменные PHPDoc:**

    @api
    @author
    @category
    @copyright
    @deprecated
    @example
    @filesource
    @global
    @ignore
    @internal
    @license
    @link
    @method
    @package
    @param
    @property
    @property-read
    @property-write
    @return
    @see
    @since
    @source
    @subpackage
    @throws
    @todo
    @uses
    @var
    @version

Подробнее про каждый тег в отдельности [на сайте PHPDoc](http://phpdoc.org/docs/latest/references/phpdoc/tags/index.html).

**SQL запросы**
Во-первых: так как у нас на сервере применяется кэширование запросов, необходимо понимать, что:

* Несколько коротких, повторяющихся запросов, в которых результат при каждом выполнении (в 99% случаев) вернёт один и тот же результат будут быстрее, чем один большой запрос, возвращающий (в 99% случаев) различные результаты.
* Запросы SELECT * FROM `goods`, select * from `goods` и select * from goods - три разных запроса, и буду занимать три строчки в кэше. Более 80% запросов написано первым стилем (SELECT * FROM `goods`) - нужно придерживаться такого стиля
* Когда вы вставляете в запрос переменную, (например, $days), вставлять её необходимо через литералы (SELECT * FROM `sample` WHERE `last_day` > '{$days}'). Запросы без литералов ({}) являются небезопасными, плюс, к тому же, если var_dump($days) в одном случае вернёт int, во втором string, в третем varchar, это снова будут три разных запроса.

Стиль написания запросов очень простой: как можно меньше JOIN'ов, UNION'ов. Если можно - минимизировать использование IN, INNER.

В нашем проекте все уже привыкли к хорошему и красивому вызову запросов. Я приведу пример:

**Так делать плохо:**

    $query = "SELECT * FROM `sample`";
    $this->query($query);

**Так делать хорошо:**

    $this->query("SELECT * FROM `sample`");

Применчание: если у вас динамический (строящийся) запрос - можно использовать первый метод.

**Вложености**
Вложености - это плохо. Кому хочется читать огромную лестницу? Никому. Это понижает ваши шансы выспаться суботним утром, так как в случае, если вашу функцию будет нужно модифицировать, именно вы будете её переделывать. Поэтому, если так уж вышло, что вам нужно перебрать много вариантов, постарайтесь написать свой код, перечитать его, понять, что можно оптимизировать, переписать, и так до того момента, пока максимальный уровень вложености не будет ниже 4.

**Пример пичальки**

    if($order['vozvrat'] == '1'){
		$orderStatus = '7';
	}else{
		if($order['confirm'] != '0'){
			if($order['confirm'] == '2'){
				$orderStatus = '3';
			}else{
				if($order['done'] == '1'){
					if(strlen($order['nakladna']) > '1' && $order['sendnakladna'] == '1' && $order['fakt_summ'] != '0'){
						if($order['confirm_money'] == '1'){
							if($order['plateg'] == '2' || $order['plateg'] == '3' || $order['plateg'] == '4'){
								$orderStatus = '8';
							}elseif($order['plateg'] == '1'){
								$orderStatus = '6';
							}
						}elseif($order['plateg'] == '1'){
							$orderStatus = '9';
						}
					}elseif($order['plateg'] == '2' || $order['plateg'] == '3' || $order['plateg'] == '4' || $order['plateg'] == '5'){
						if($order['confirm_money'] == '1'){
							$orderStatus = '6';
						}else{
							$orderStatus = '4';
						}
					}elseif($order['plateg'] == '1'){
						$orderStatus = '10';
					}
				}elseif($order['plateg'] == '1' && strlen($order['nakladna']) > '1' && $order['sendnakladna'] != '0'){
					$orderStatus = '4';
				}else{
					$orderStatus = '2';
				}
			}
		}else{
			$orderStatus = '1';
		}
	}

**Пример хороошего кода** будет позже

**Отступы**
Отступы - это хорошо, это нужно. Но как говорит поговорка: "що занадто - то нездраво". Примеры ниже:

**Плохо:**

    function doDomething ( $param )
    {
        $someVar = 'foo' ;
        $someVar = $someVar . $someVar ;
    }



**Тоже плохо**

    function doSomething ($param) {
        $someVar = 'foo';
        $someVar = $someVar.$someVar;
    }

Плохо, из-за лишних отступов вокруг скобок с параметрами

**Правильно**

    function doSomething($param){
        $someVar = 'foo';
        $someVar = $someVar.$someVar;
    }



**Рефакторинг и повторное использование кода**
Перед тем, как написать свой велосипед, проверьте, что такой велосипед (или целый велопарк) перед вами не написали. Если есть такая возможность - модифицируйте код, написаный до вас в код, необходимый вам, не ломающий предыдущий функционал. Например, мне потребовалось считать сумму всех заказов клиента. Дмитрий написал такую функцию, и вызвав с нужными параметрами, я получил нужные мне данные. Но тут мне потребовалось получить сумму по заказам, которые не были удалены, а функция Дмитрия возвращает сумму всех заказов, без исключения. Что сделал я:

**Старая функция**

    function getAllUserOrders($userID){
		$query = $this->query("SELECT * FROM `history` WHERE `userid` = '{$userID}' ORDER BY `id` DESC");
		$commSumm = 0;
		$count = 0;
		$paymentTypes = $this->getPaytypes(true);
		while($order = $query->fetch_assoc()){
			if($order['done'] == '1'){
				$order['summ'] = $order['fakt_summ'];
				$commSumm += $order['fakt_summ'];
			}else{
				$summ = $this->getSumm($order['id']);
				$order['summ'] = $summ;
				$commSumm += $summ;
			}
			$order['date'] = date('Y-m-d', $order['displayorder']);
			$order['hour'] = date('H', $order['displayorder']);
			$order['minute'] = date('i', $order['displayorder']);
			if($paymentTypes){
				foreach($paymentTypes as $paymentType){
					if($paymentType['id'] == $order['plateg']){
						$payType = $paymentType;
					}
				}
				if($payType['id']){
					$order['plategType'] = $payType['name'];
				}else{
					$order['plategType'] = "Ошибка! Не найден такой способ оплаты!";
				}
			}else{
				$order['plategType'] = "Ошибка! Не найден такой способ оплаты!";
			}
			$allOrders[] = $order;
			$count += 1;
		}
		$allOrders['commSumm'] = $commSumm;
		$allOrders['count'] = $count;
		return $allOrders;
	}

**Модифицированая функция**

    function getAllUserOrders($userID, $deleted = true){
		$deleted = ($deleted == true ? '' : " AND `trash` = '0'");
		$query = $this->query("SELECT * FROM `history` WHERE `userid` = '{$userID}'".$deleted." ORDER BY `id` DESC");
		$commSumm = 0;
		$count = 0;
		$paymentTypes = $this->getPaytypes(true);
		while($order = $query->fetch_assoc()){
			if($order['done'] == '1'){
				$order['summ'] = $order['fakt_summ'];
				$commSumm += $order['fakt_summ'];
			}else{
				$summ = $this->getSumm($order['id']);
				$order['summ'] = $summ;
				$commSumm += $summ;
			}
			$order['date'] = date('Y-m-d', $order['displayorder']);
			$order['hour'] = date('H', $order['displayorder']);
			$order['minute'] = date('i', $order['displayorder']);
			if($paymentTypes){
				foreach($paymentTypes as $paymentType){
					if($paymentType['id'] == $order['plateg']){
						$payType = $paymentType;
					}
				}
				if($payType['id']){
					$order['plategType'] = $payType['name'];
				}else{
					$order['plategType'] = "Ошибка! Не найден такой способ оплаты!";
				}
			}else{
				$order['plategType'] = "Ошибка! Не найден такой способ оплаты!";
			}
			$allOrders[] = $order;
			$count += 1;
		}
		$allOrders['commSumm'] = $commSumm;
		$allOrders['count'] = $count;
		return $allOrders;
	}

Как вы видите, модифицированы были только первые три строки, чего было достаточно, для того, чтобы добавить функции новый, необходимый мне, функционал. Также в случае использовании даной функции другими методами в старом контексте, SQL запрос не будет модифицирован, кэш запроса не будет изменён, и скорость работы функции будет как и прежде. Переменная $deleted у меня в функции всегда является статичной, и имеет тип string, поэтому я позволил себе вклеить её в SQL запрос.

**Комментирование**
С комментариями та же история, что и с отступами. Если вы хотите прокоментировать работу вашей функции, напишите перед ней PHPDoc блок с описанием функции. В случае, если вы хотите прокоментировать работу участка кода вашей функции, тогда:

* Излишне не комментируйте код: лучше сгрупируйте схожие участки кода, и прокомментируйте их одним красивым комментарием.
* Краткость - сестра таланта. Чем короче и точнее ваш коментарий сможет описать происходящее, тем лучше всем (и вам в том числе).

из выше изложеного можно понять, что плохой комментатор, это комментатор, который написал это:

    function doSomething($param){
        //Проверяем, что $param не пустой
        if($param != ''){
            //и если он не пустой
            $param = $param + 1;
            //добавляем к нему единичку
        }else{
            //в инном случае
            $param = $param - 1;
        }
        return $param;
    }

а хороший написал это:

    /**
    *Функция проверяет, что входящий параметр не пустой, и добавляет к нему 1, или отнимает от него 1
    ...
    */
    function doSomething($param){
        //Проверяем, что параметр не пустой
        if($param != ''){
            $param = $param + 1;
        }else{
            $param = $param - 1;
        }
        return $param;
    }

**Названия**
Если вы пишите функцию, которая будет использоваться минимум 2 раза, дайте ей красивое имя, которое отражает всю суть вещей: что делает функция, и с чем.


**Хорошее название**: searchUser($user)
**Плохое**: functionUserFind($user)

также и с переменными:
**Хорошее название**: $summ = '960'; //сумма заказов
**Плохое название**: $totalMoney = '960'; //сумма заказов

**Важно**: переменные, которые являются входными параметрами функции, нужно называть так, чтобы они отражали всю суть вещей!!!


**Политика: разделяй и властвуй**
..дописать

##Пока что всё, в скором времени список возможно будет дополнен
