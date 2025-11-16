# Medicinereminder2

## Overview
Medicinereminder2 is a simple, privacy-focused medication schedule tracker built with [Laravel](https://laravel.com). It helps users remember to take their pills, track daily progress, and manage their medication setup—all without requiring any personal information or user accounts.

## Features
- Add, edit, and delete pills with quantity and time of day (morning, afternoon, evening)
- Mark pills as taken for the day
- Reset daily progress
- Export and import your setup and progress as JSON for easy sharing or backup
- No tracking, analytics, or personal data collection - fully anonymous
- Responsive, modern UI with Tailwind CSS

## Privacy & Anonymity
Medicinereminder2 is designed to be extremely anonymous:
- No user registration or login required
- All data is stored in encrypted sessions. 
- No tracking cookies, analytics, or third-party scripts
- You control your data—export, import, or clear it at any time

## Usage
1. Set up your medication schedule on the Setup page
2. Track your daily progress on the Schedule page
3. Export your setup to share with a healthcare provider or import a new schedule
4. All features work without any personal information

## Installation
1. Clone the repository
2. Install dependencies: `composer install` and `npm install`
3. Copy `.env.example` to `.env` and set up your environment
4. Run migrations: `php artisan migrate`
5. Start the server: `php artisan serve`


## Special Thanks 

- **[Povilas Korop](https://github.com/LaravelDaily)** from [Laravel Daily](https://laraveldaily.com/) - for the comprehensive security and code quality review that helped make this application more secure and privacy-focused.


## License

MIT License

Copyright (c) 2025 Bogi99@gmail.com

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
