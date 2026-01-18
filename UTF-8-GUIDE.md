# Guia Completo: UTF-8 sem BOM

## 🚨 Problema: BOM (Byte Order Mark)

BOM são caracteres invisíveis no início de arquivos que podem corromper JSON e outras comunicações.

## ✅ Soluções Implementadas

### 1. **Função removeBOM()**
```php
private function removeBOM(string $string): string
{
    // Remove BOM UTF-8 (EF BB BF)
    $bom = pack('H*', 'EFBBBF');
    $string = preg_replace("/^$bom/", '', $string);
    
    // Remove outros BOMs (UTF-16, UTF-32)
    $boms = [
        pack('H*', 'FEFF'),  // UTF-16 BE
        pack('H*', 'FFFE'),  // UTF-16 LE
        pack('H*', '0000FEFF'), // UTF-32 BE
        pack('H*', 'FFFE0000'), // UTF-32 LE
    ];
    
    foreach ($boms as $bom) {
        $string = preg_replace("/^$bom/", '', $string);
    }
    
    // Garante UTF-8 válido
    if (!mb_check_encoding($string, 'UTF-8')) {
        $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
    }
    
    return trim($string);
}
```

### 2. **Aplicação no Controller**
- `$rawInput = $this->removeBOM($rawInput);`
- Log mostra "BOM removed" para debug

### 3. **Script de Teste**
- `/test-json` mostra se BOM foi detectado
- Compara original vs limpo

## 🔧 Como Garantir UTF-8 sem BOM

### **Para Arquivos PHP:**

#### **VS Code:**
1. Abrir arquivo
2. Clicar em `UTF-8` no canto inferior direito
3. Selecionar `Save with Encoding`
4. Escolher `UTF-8`

#### **Notepad++:**
1. `File` → `Save As...`
2. Em `Encoding`, selecionar `UTF-8`
3. **NÃO** selecionar `UTF-8 BOM`

#### **Sublime Text:**
1. `File` → `Save with Encoding` → `UTF-8`
2. Ou adicionar ao settings: `"default_encoding": "UTF-8"`

### **Para Requisições HTTP:**

#### **Headers Corretos:**
```http
Content-Type: application/json; charset=utf-8
Accept: application/json
```

#### **cURL:**
```bash
curl -X POST http://servidor/transacoes/criar \
  -H "Content-Type: application/json; charset=utf-8" \
  -d '{"valor": 100.50, "de": 1, "para": 2}'
```

#### **JavaScript/Fetch:**
```javascript
fetch('/transacoes/criar', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json; charset=utf-8'
    },
    body: JSON.stringify(data)
});
```

### **Para Banco de Dados:**

#### **MySQL:**
```sql
-- Tabela com UTF-8
CREATE TABLE transacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    valor DECIMAL(10,2) NOT NULL,
    de INT NOT NULL,
    para INT NOT NULL,
    status VARCHAR(20) DEFAULT 'pending'
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### **Configuração CodeIgniter:**
```php
// app/Config/Database.php
public $default = [
    'charset'  => 'utf8mb4',
    'DBCollat' => 'utf8mb4_unicode_ci',
];
```

## 🧪 Como Testar

### **1. Testar com BOM:**
```bash
# Criar arquivo com BOM
echo '{"valor": 100.50, "de": 1, "para": 2}' > with_bom.json

# Adicionar BOM manualmente
printf '\xEF\xBB\xBF' > bom.txt
cat with_bom.json >> bom.txt

# Enviar com BOM
curl -X POST http://servidor/test-json \
  -H "Content-Type: application/json" \
  --data-binary @bom.txt
```

### **2. Verificar Resposta:**
```json
{
    "bom_detected": true,
    "raw_input_original": "﻿{\"valor\": 100.50}",
    "raw_input_clean": "{\"valor\": 100.50}",
    "json_parsed": {"valor": 100.50, "de": 1, "para": 2}
}
```

## 🚀 Comandos Úteis

### **Verificar BOM em Arquivos:**
```bash
# Detectar BOM
file -bi arquivo.json
# Se tiver BOM, mostrará: charset=utf-8; charset=bom

# Remover BOM
sed -i '1s/^\xEF\xBB\xBF//' arquivo.json

# Verificar hex
xxd arquivo.json | head -1
# BOM UTF-8: efbbbf
```

### **PHP:**
```php
// Verificar se string tem BOM
function hasBOM($string) {
    return substr($string, 0, 3) === "\xEF\xBB\xBF";
}

// Converter para UTF-8 sem BOM
function toUTF8WithoutBOM($string) {
    $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
    return preg_replace('/^\xEF\xBB\xBF/', '', $string);
}
```

## 📋 Checklist de Verificação

- [ ] **Arquivos PHP salvos como UTF-8 sem BOM**
- [ ] **Headers HTTP com charset=utf-8**
- [ ] **Banco de dados configurado para utf8mb4**
- [ ] **Função removeBOM() aplicada ao input**
- [ ] **Logs mostrando "BOM removed"**
- [ ] **Teste com /test-json funcionando**

## 🎯 Resultado Esperado

Com essas configurações:
- ✅ JSON parseado corretamente
- ✅ Sem erros de "JSON inválido"
- ✅ Comunicação estável entre cliente/servidor
- ✅ Logs mostrando processamento correto

O sistema agora está protegido contra problemas de BOM!
