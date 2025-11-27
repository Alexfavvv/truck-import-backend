#!/bin/bash

# Скрипт для тестирования API корзины
# Использование: ./test-cart-api.sh

BASE_URL="http://localhost/api/v1/client"
TOKEN=""

# Цвета для вывода
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}=== Тестирование API корзины ===${NC}\n"

# 1. Создание токена
echo -e "${YELLOW}1. Создание токена для пользователя...${NC}"
TOKEN_OUTPUT=$(./vendor/bin/sail artisan tinker --execute="use App\Models\User; \$user = User::where('email', 'admin@example.com')->first(); if(\$user) { \$token = \$user->createToken('test-token')->plainTextToken; echo \$token; }")
TOKEN=$(echo "$TOKEN_OUTPUT" | grep -E "^[0-9]+\|" | head -1)

if [ -z "$TOKEN" ]; then
    echo "Ошибка: Не удалось создать токен"
    exit 1
fi

echo -e "${GREEN}Токен создан: ${TOKEN:0:20}...${NC}\n"

# 2. Получение корзины
echo -e "${YELLOW}2. GET /cart - Получение корзины${NC}"
curl -s -X GET "${BASE_URL}/cart" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" | jq '.' 2>/dev/null || curl -s -X GET "${BASE_URL}/cart" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
echo -e "\n"

# 3. Получение списка продуктов (чтобы узнать ID продукта)
echo -e "${YELLOW}3. GET /products - Получение списка продуктов${NC}"
PRODUCTS=$(curl -s -X GET "${BASE_URL}/products" -H "Accept: application/json")
echo "$PRODUCTS" | jq '.[0:2]' 2>/dev/null || echo "$PRODUCTS" | head -20
echo -e "\n"

# Получаем ID первого продукта
PRODUCT_ID=$(echo "$PRODUCTS" | grep -o '"id":[0-9]*' | head -1 | grep -o '[0-9]*' | head -1)

if [ -z "$PRODUCT_ID" ]; then
    echo "Предупреждение: Не удалось получить ID продукта. Пропускаем тесты добавления товара."
else
    echo -e "${YELLOW}4. POST /cart/items - Добавление товара (product_id: $PRODUCT_ID, quantity: 2)${NC}"
    curl -s -X POST "${BASE_URL}/cart/items" \
      -H "Accept: application/json" \
      -H "Content-Type: application/json" \
      -H "Authorization: Bearer $TOKEN" \
      -d "{\"product_id\": $PRODUCT_ID, \"quantity\": 2}" | jq '.' 2>/dev/null || curl -s -X POST "${BASE_URL}/cart/items" \
      -H "Accept: application/json" \
      -H "Content-Type: application/json" \
      -H "Authorization: Bearer $TOKEN" \
      -d "{\"product_id\": $PRODUCT_ID, \"quantity\": 2}"
    echo -e "\n"

    # Получаем ID элемента корзины
    sleep 1
    CART=$(curl -s -X GET "${BASE_URL}/cart" \
      -H "Accept: application/json" \
      -H "Authorization: Bearer $TOKEN")
    CART_ITEM_ID=$(echo "$CART" | grep -o '"id":[0-9]*' | head -1 | grep -o '[0-9]*' | head -1)

    if [ ! -z "$CART_ITEM_ID" ]; then
        echo -e "${YELLOW}5. PUT /cart/items/$CART_ITEM_ID - Обновление количества товара (quantity: 5)${NC}"
        curl -s -X PUT "${BASE_URL}/cart/items/$CART_ITEM_ID" \
          -H "Accept: application/json" \
          -H "Content-Type: application/json" \
          -H "Authorization: Bearer $TOKEN" \
          -d '{"quantity": 5}' | jq '.' 2>/dev/null || curl -s -X PUT "${BASE_URL}/cart/items/$CART_ITEM_ID" \
          -H "Accept: application/json" \
          -H "Content-Type: application/json" \
          -H "Authorization: Bearer $TOKEN" \
          -d '{"quantity": 5}'
        echo -e "\n"

        echo -e "${YELLOW}6. DELETE /cart/items/$CART_ITEM_ID - Удаление товара из корзины${NC}"
        curl -s -X DELETE "${BASE_URL}/cart/items/$CART_ITEM_ID" \
          -H "Accept: application/json" \
          -H "Authorization: Bearer $TOKEN" | jq '.' 2>/dev/null || curl -s -X DELETE "${BASE_URL}/cart/items/$CART_ITEM_ID" \
          -H "Accept: application/json" \
          -H "Authorization: Bearer $TOKEN"
        echo -e "\n"
    fi
fi

# 7. Очистка корзины
echo -e "${YELLOW}7. DELETE /cart - Очистка корзины${NC}"
curl -s -X DELETE "${BASE_URL}/cart" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" | jq '.' 2>/dev/null || curl -s -X DELETE "${BASE_URL}/cart" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
echo -e "\n"

echo -e "${GREEN}=== Тестирование завершено ===${NC}"


