const fs = require('fs');
const path = require('path');

// Имя файла
const fileName = 'MyBusiness-export-MyClients-73835-2025.09.24-07_23.Csv';
const filePath = path.join(__dirname, fileName);

// Читаем файл
const fileContent = fs.readFileSync(filePath, 'utf8');

// Парсим CSV
const lines = fileContent.trim().split('\n');

// Функция для парсинга CSV строки с учетом кавычек
function parseCSVLine(line) {
    const result = [];
    let current = '';
    let inQuotes = false;
    
    for (let i = 0; i < line.length; i++) {
        const char = line[i];
        
        if (char === '"') {
            inQuotes = !inQuotes;
        } else if (char === ',' && !inQuotes) {
            result.push(current.trim());
            current = '';
        } else {
            current += char;
        }
    }
    result.push(current.trim());
    
    return result;
}

// Функция для экранирования значений для SQL
function escapeSQLValue(value) {
    if (!value || value === '-' || value === '') {
        return 'NULL';
    }
    // Экранируем одинарные кавычки и обратные слеши
    return "'" + value.replace(/\\/g, '\\\\').replace(/'/g, "''") + "'";
}

// Генерируем SQL INSERT
const sqlInserts = [];
const timestamp = 'NOW()';
let skippedCount = 0;

for (let i = 1; i < lines.length; i++) {
    if (!lines[i].trim()) continue;
    
    const values = parseCSVLine(lines[i]);
    
    const nameValue = values[0];
    const phoneValue = values[1];
    const emailValue = values[2];
    
    // Пропускаем записи без имени И без телефона
    if ((!nameValue || nameValue === '-') && (!phoneValue || phoneValue === '-')) {
        skippedCount++;
        continue;
    }
    
    // Если нет имени, используем телефон или email как имя
    let finalName = nameValue && nameValue !== '-' ? nameValue : 
                    (phoneValue && phoneValue !== '-' ? phoneValue : 
                    (emailValue && emailValue !== '-' ? emailValue : 'Клієнт'));
    
    const name = escapeSQLValue(finalName);
    const phone = escapeSQLValue(phoneValue);
    const email = escapeSQLValue(emailValue);
    
    // Генерируем дефолтный пароль (хеш от 'password')
    const password = "'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'";
    
    const sql = `INSERT INTO users (name, email, phone, password, role, is_active, created_at, updated_at) 
VALUES (${name}, ${email}, ${phone}, ${password}, 'client', 1, ${timestamp}, ${timestamp});`;
    
    sqlInserts.push(sql);
}

// Выводим результат
console.log('-- SQL INSERT statements for users table');
console.log('-- Total records: ' + sqlInserts.length);
console.log('-- Skipped records: ' + skippedCount);
console.log('-- Default password for all users: "password"');
console.log('');
sqlInserts.forEach(sql => console.log(sql));

// Сохраняем в файл
const outputFile = 'insert_users.sql';
fs.writeFileSync(outputFile, sqlInserts.join('\n'), 'utf8');
console.log(`\n-- Saved to ${outputFile}`);