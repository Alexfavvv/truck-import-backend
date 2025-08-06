<h2>Новое сообщение с формы обратной связи</h2>

<p><strong>Телефон:</strong> {{ $data['phone'] }}</p>
{{--<p><strong>URL страницы:</strong> <a href="{{ $data['url'] }}">{{ $data['url'] }}</a></p>--}}
@if(!empty($data['name']))
    <p><strong>Имя:</strong> {{ $data['name'] }}</p>
@endif

<p><strong>Согласие на обработку данных:</strong> {{ $data['consentPrivacy'] ? 'Да' : 'Нет' }}</p>
