# ZSZ-Rekrutacja API
REST API dla aplikacji ZSZ-Rekrutacja

## Informacje o projekcie
ZSZ-Rekrutacja to aplikacja, mająca na celu umożliwić szkole wygodne informowanie przyszłych uczniów o przebiegu rekrutacji. W tym repozytorium znajduje się jedna z części tej aplikacji, czyli API. API stosuje zasady REST. Komunikacja wykorzystuje format JSON.

## Dokumentacja

### Uwierzytelnienie

Na samym początku należy zapoznać się z mechanizmem uwierzytelnienia.

Zalogować do aplikacji można się na jedno z domyślnie istniejących kont (na chwilę obecną istnieją trzy testowe konta: admin, admin2 oraz dyrektor; hasła do każdego z nich są takie same, jak nazwy użytkowników). Aby to zrobić, trzeba wysłać zapytanie POST /login. W treści zapytania należy zawrzeć następujące pola:

| Nazwa pola | Opis pola                                          | Typ   |
| ---------- | -------------------------------------------------- | ----- |
| username   | Nazwa użytkownika, na którego chcemy się zalogować | Tekst |
| password   | Hasło użytkownika                                  | Tekst |

Jeżeli dane zostaną podane poprawnie, użytkownik zostanie zalogowany. W odpowiedzi zwrócony zostanie token uwierzytelniający. Należy go odtąd wysyłać w nagłówku każdego zapytania w kluczu o nazwie **X-AUTH-TOKEN**. Przykład:

```
X-AUTH-TOKEN: tayzOXV4zUgEqMFJsdxFK69gLIma3WCdzuulZeV6762b0Al1qK0ReaiJmyrPABn3uzZp9mvLiqNn4JJ5XWzYC5Fs1U0Qm2owMI9LU3SVA0wqj8Fzvl99X6uWnlpfLX6I
```

Przesłanie tokenu umożliwi uwierzytelnienie użytkownika.

Token może zostać unieważniony podczas wylogowywania użytkownika - po wylogowaniu jest usuwany i nie można już przy jego pomocy uzyskać dostępu do konta. Aby to zrobić, należy wysłać zapytanie GET /logout.

Każdy token posiada także swój czas ważności. Domyślnie jest to 10 minut. Jeżeli użytkownik nie wyloguje się samodzielnie (poprzez /logout), to token i tak utraci swoją ważność. Jeżeli użytkownik korzysta z aplikacji, czas ważności tokenu automatycznie się przedłuża (aby nie wylogować użytkownika nagle, podczas pracy).

### Zasoby

W skład aplikacji wchodzą następujące zasoby:

- posts - zasób ten przechowuje wiadomości adresowane do użytkowników aplikacji, które są dodawane przez administratora. Post opisują następujące pola:

  | Nazwa pola | Opis pola                                                    | Typ                            | Wymagane                                                     |
  | ---------- | ------------------------------------------------------------ | ------------------------------ | ------------------------------------------------------------ |
  | ordinal    | Numer porządkowy; określa pozycję na liście wszystkich postów (im mniejszy numer, tym wcześniejsza pozycja) | Liczba całkowita               | Tak                                                          |
  | topic      | Temat; krótki tytuł wiadomości                               | Tekst (maksymalnie 255 znaków) | Tak                                                          |
  | content    | Treść wiadomości                                             | Tekst                          | Tak                                                          |
  | active     | Określa, czy post jest aktywny; jeżeli nie jest aktywny, nie będzie prezentowany użytkownikom | Wartość logiczna               | Nie (w przypadku niepodania - wartość fałszywa, w przeciwnym wypadku - prawdziwa) |

- schools - zasób ten gromadzi informacje na temat szkoły, w której aktualnie uczy się subskrybent. Szkołę opisują następujące pola:

  | Nazwa pola | Opis pola    | Typ                            | Wymagane |
  | ---------- | ------------ | ------------------------------ | -------- |
  | name       | Nazwa szkoły | Tekst (maksymalnie 255 znaków) | Tak      |

  Szkoła dysponuje także właściwością accepted, która określa, czy szkoła została zaakceptowana i dostępna do wglądu dla wszystkich użytkowników (kiedy uczeń doda szkołę, administrator musi ją najpierw zweryfikować, nim zostanie ona udostępniona do wyboru szerszej grupie użytkowników). Opisuje ją tylko jedno pole:

  | Nazwa pola | Opis pola                                                    | Typ              | Wymagane                                                     |
  | ---------- | ------------------------------------------------------------ | ---------------- | ------------------------------------------------------------ |
  | accepted   | Określa, czy szkoła została zaakceptowana przez administratora i umieszczona na ogólnodostępnej liście. | Wartość logiczna | Nie (w przypadku niepodania - wartość fałszywa, w przeciwnym wypadku - prawdziwa) |

  Właściwość ta (*accepted*) została odłączona od zwykłych właściwości (*name*) ze względu na to, że tylko administrator ma prawo ustawić ją według własnych potrzeb.

- subscribers - zasób ten przechowuje informacje na temat danego subskrybenta. Subskrybentem jest użytkownik, który wyraził chęć informowania go o przebiegu rekrutacji i zgodził się podać swoje dane.

  | Nazwa pola | Opis pola                                                    | Typ                            | Wymagane                                                     |
  | ---------- | ------------------------------------------------------------ | ------------------------------ | ------------------------------------------------------------ |
  | first_name | Imię                                                         | Tekst (maksymalnie 255 znaków) | Tak                                                          |
  | surname    | Nazwisko                                                     | Tekst (maksymalnie 255 znaków) | Tak                                                          |
  | email      | E-mail                                                       | E-mail                         | Tak                                                          |
  | school     | Identyfikator szkoły; identyfikator zostanie zwrócony podczas dodania szkoły | Identyfikator liczbowy         | Tak                                                          |
  | confirmed  | Przechowuje informację na temat tego, czy subskrybent potwierdził rejestrację, czy nie. | Wartość logiczna               | Tego pola nie podaje się w formularzu, jest ono obsługiwane przez API w inny sposób. |

  Subskrybenci dysponują także innymi właściwościami - a mianowicie tokenami bezpieczeństwa, które są generowane podczas rejestracji użytkownika. Subskrybent będzie musiał podać odpowiedni token przy potwierdzeniu rejestracji konta oraz przy jego ewentualnym usuwaniu. Tokenów tych nie wysyła się w treści zapytania, ale w adresie URL.

  | Nazwa pola        | Opis pola                                                    | Typ                             |
  | ----------------- | ------------------------------------------------------------ | ------------------------------- |
  | unsubscribe_token | Token, który należy podać podczas usuwania subskrybcji       | Tekst (32 znaki alfanumeryczne) |
  | confirm_token     | Token, który należy podać podczas potwierdzenia rejestracji konta | Tekst (32 znaki alfanumeryczne) |

Każdy zasób posiada dodatkowo pole **id**, które służy do jego identyfikacji.

### Akcje

Na wyżej wymienionych zasobach wykonuje się akcje. Można przykładowo: dodać nowy, zedytować istniejący zasób, usunąć go, albo odczytać informacje jego temat. Rodzaje akcji i uprawnienia potrzebne do ich wykonania, zależą już wyłącznie od rodzaju zasobu. Lista pól, które należy przesłać podczas poszczególnych akcji, znajduje się wyżej, w sekcji *Zasoby*. Poniżej znajduje się lista akcji, które można wykonać na poszczególnych zasobach:

- posts

  | Metoda HTTP | Ścieżka                  | Opis                                                         | Wymagane uprawnienia                         | Zwracany status HTTP                                         |
  | ----------- | ------------------------ | ------------------------------------------------------------ | -------------------------------------------- | ------------------------------------------------------------ |
  | GET         | /posts                   | Pobiera listę wszystkich postów. Domyślnie wypisywane są dwa. Aby to zmienić, należy zapoznać się z sekcją *Stronnicowanie*. Liczba wszystkich istniejących postów znajduje się w polu **count** odpowiedzi. Natomiast dane prezentujące poszczególne posty znajdują się w polu **data** odpowiedzi. Administratorom zwracana jest lista wszystkich postów (zarówno tych aktywnych, jak i nieaktywnych), natomiast użytkownicy anonimowi otrzymują dostęp wyłącznie do postów aktywnych. | Każdy może wykonać tę operację               | **200 (OK)** w razie powodzenia                              |
  | GET         | /posts/{id}              | Pobiera informacje na temat konkretnego posta (w miejsce {id} należy wstawić liczbowy identyfikator posta). | Każdy może wykonać tę operację               | **200 (OK)** w razie powodzenia; **404 (Not Found)** w przypadku, gdy zasób o danym id nie istnieje |
  | POST        | /posts                   | Dodaje nowy post. Wymagane do wysłania pola opisane zostały wcześniej, w sekcji *Zasoby*. | Tylko administrator może wykonać tę operację | **201 (Created)** w razie powodzenia; **400 (Bad Request)** w razie błędu walidacji |
  | PUT         | /posts/{id}              | Aktualizuje post o konkretnym id (w miejsce {id} należy wstawić liczbowy identyfikator posta). Wymagane jest wysłanie wszystkich obowiązkowych pól (zostały opisane wcześniej, w sekcji *Zasoby*). | Tylko administrator może wykonać tę operację | **200 (OK)** w razie powodzenia; **404 (Not Found)** w przypadku, gdy post o danym id nie istnieje; **400 (Bad Request)** w razie błędu walidacji |
  | DELETE      | /posts/{id}              | Usuwa post o konkretnym id (w miejsce {id} należy wstawić liczbowy identyfikator posta). Nie trzeba wysyłać żadnych pól. | Tylko administrator może wykonać tę operację | **200 (OK)** w razie powodzenia; **404 (Not Found)** w przypadku, gdy post o podanym id nie istnieje |
  | POST        | /posts/{id}/notification | Dodaje powiadomienie o poście o określonym id do wysyłki.    | Tylko administrator może wykonać tę operację | **200 (OK)** w razie powodzenia; **404 (Not Found)** w przypadku, gdy post o podanym id nie istnieje |

- schools

  | Metoda HTTP | Ścieżka                | Opis                                                         | Wymagane uprawnienia                          | Zwracany status HTTP                                         |
  | ----------- | ---------------------- | ------------------------------------------------------------ | --------------------------------------------- | ------------------------------------------------------------ |
  | GET         | /schools               | Pobiera informacje na temat wszystkich szkół. Domyślnie wypisywane są dwie. Aby to zmienić, należy zapoznać się z sekcją *Stronnicowanie*. Liczba wszystkich istniejących szkół znajduje się w polu **count** odpowiedzi. Natomiast dane prezentujące poszczególne szkoły znajdują się w polu **data** odpowiedzi. Administratorom zwracana jest pełna lista szkół, natomiast użytkownikom anonimowych tylko lista szkół zatwierdzonych (zaakceptowanych). | Każdy może wykonać tę operację                | **200 (OK)** w razie powodzenia                              |
  | GET         | /schools/{id}          | Pobiera informacje na temat konkretnej szkoły (w miejsce {id} należy wstawić liczbowy identyfikator szkoły). | Każdy może wykonać tę operację                | **200 (OK)** w razie powodzenia; **404 (Not Found)** w razie, gdy szkoła o podanym id nie istnieje |
  | POST        | /schools               | Dodaje nową szkołę. Wymagane jest wysłanie wszystkich obowiązkowych pól (zostały one opisane w sekcji *Zasoby*). Szkoła dodawana przez administratora jest domyślnie zaakceptowana, natomiast taka dodawana przez użytkownika nie jest domyślnie zaakceptowana - oczekuje na zatwierdzenie przez administratora. | Każdy może wykonać tę operację                | **201  (Created)** w razie powodzenia; **400 (Bad Request)** w razie błędu walidacji |
  | PUT         | /schools/{id}          | Aktualizuje konkretną szkołę (w miejsce {id} należy wstawić liczbowy identyfikator szkoły). | Tylko administrator może wykonać tę operację  | **200 (OK)** w razie powodzenia; **400 (Bad Request)** w razie błędu walidacji; **404 (Not Found)** w przypadku, gdy szkoła o danym id nie istnieje |
  | DELETE      | /schools/{id}          | Usuwa konkretną szkołę (w miejsce {id} należy wstawić liczbowy identyfikator szkoły). | Tylko administrator może wykonać tę operację. | **200 (OK)** w razie powodzenia; **404 (Not Found)** w przypadku, gdy szkoła o danym id nie istnieje |
  | PUT         | /schools/{id}/accepted | Zmienia status "zaakceptowany" szkoły na przesłany w zapytaniu. Opis pól przesyłanych w tej właściwości jest dostępny w sekcji *Zasoby*.gT | Tylko administrator może wykonać tę operację  | **200 (OK)** w razie powodzenia; **400 (Bad Request)** w razie błędu walidacji; **404 (Not Found)** w przypadku, gdy szkoła o danym id nie istnieje |

- subscribers

  | Metoda HTTP | Ścieżka                      | Opis                                                         | Wymagane uprawnienia                          | Zwracany status HTTP                                         |
  | ----------- | ---------------------------- | ------------------------------------------------------------ | --------------------------------------------- | ------------------------------------------------------------ |
  | GET         | /subscribers                 | Pobiera informacje na temat wszystkich subskrybentów. Domyślnie wypisywane są dwaj subskrybenci. Aby to zmienić, należy zapoznać się z sekcją *Stronnicowanie*. Liczba wszystkich istniejących postów znajduje się w polu **count** odpowiedzi. Natomiast dane prezentujące poszczególnych subskrybentów znajdują się w polu **data** odpowiedzi. | Tylko administrator może wykonać tę operację  | **200 (OK)** w razie powodzenia                              |
  | GET         | /subscribers/{id}            | Pobiera informacje na temat konkretnego subskrybenta (w miejsce {id} należy wstawić liczbowy identyfikator szkoły). | Tylko administrator może wykonać tę operację  | **200 (OK)** w razie powodzenia; **404 (Not Found)** w przypadku, gdy subskrybent o podanym id nie istnieje |
  | POST        | /subscribers                 | Dodaje nowego subskrybenta. Lista wymaganych pól znajduje się w sekcji *Zasoby*. | Każdy może wykonać tę operację                | **201 (Created)** w razie powodzenia; **400 (Bad Request)** w przypadku, gdy subskrybent o podanym id nie istnieje |
  | DELETE      | /subscribers/{id}            | Usuwa subskrybenta o podanym id.                             | Tylko administrator może wykonać tę operację. | **200 (OK)** w razie powodzenia; **404 (Not Found)** w przypadku, gdy subskrybent o podanym id nie istnieje. |
  | DELETE      | /subscribers/{token}         | Usuwa subskrybenta o podanym tokenie subskrybcji. Token ten (nie mylić z tokenem do uwierzytelniania użytkowników, tj. administratorów!) jest łańcuchem składającym się z małych, dużych liter oraz cyfr i ma długość dokładnie 32 znaków. | Każdy może wykonać tę operację.               | **200 (OK)** w razie powodzenia; **404 (Not Found)** w przypadku, gdy subskrybent o podanym id nie istnieje. |
  | GET         | /subscribers/confirm/{token} | Potwierdza rejestrację subskrybenta przy pomocy tokenu **confirm_token**. | Każdy może wykonać tę operację                | **200 (OK)** w razie powodzenia; **404 (Not Found)** w przypadku, gdy subskrybent o podanym tokenie nie istnieje. |

### Statusy HTTP odpowiedzi

Na każde zapytanie klienta serwer udziela odpowiedzi. Odpowiedzi te mają taką cechę, jak status - mówi on, czy żądanie klienta zostało spełnione, czy może wystąpiły jakieś problemy. Niniejsze API posługuje się następującymi rodzajami statusów:

- **200 (OK)** - powodzenie wykonania operacji pobierania, aktualizacji lub usuwania danych oraz wylogowania użytkownika
- **201 (Created)** - powodzenie wykonania operacji dodania danych
- **400 (Bad Request)** - niepowodzenie wykonania operacji na wskutek błędnie utworzonego zapytania (na przykład błędu walidacji danych wysyłanych do API)
- **401 (Unauthorized)** - niepowodzenie wykonania operacji na wskutek podania niewłaściwego tokenu uwierzytelniającego lub braku podania tego tokenu w zapytaniach tego wymagających (przykładowo, aby uzyskać dostęp do listy subskrybentów, użytkownik anonimowy powinien się zalogować)
- **404 (Not Found)** - niepowodzenie wykonania operacji ze względu na to, że zasób nie istnieje

### Dodatkowe informacje w odpowiedzi

W odpowiedzi serwera, przy akcjach, które tego wymagają, zwrócone mogą zostać także dodatkowe informacje. Oto pola, które mogą się tam znaleźć:

| Nazwa pola | Opis                                            | Typ danych                   |
| ---------- | ----------------------------------------------- | ---------------------------- |
| message    | Wiadomość opisująca stan wykonania się operacji | Tekst                        |
| errors     | Lista błędów (na przykład walidacji)            | Tablica elementów tekstowych |

### Stronnicowanie

Stronnicowanie jest stosowane w przypadku akcji zwracających listę wielu zasobów (na przykład listę wszystkich szkół). Zapobiega ono zwracaniu zbyt dużej ilości danych na raz (w tym danych, które nie interesują użytkownika). Domyślnie zwracane są dwa pierwsze rekordy. Aby to zmienić, należy posłużyć się parametrami adresu URL. Są to:

- offset - przesunięcie, określa który rekord zostanie wypisany jako pierwszy. Domyślnie jest to 0, a więc pierwszy rekord w bazie danych jest też pierwszym zwracanym przez API.
- limit - liczba zwracanych rekordów. Domyślnie zwracane są wszystkie rekordy.

Przykład: przygotowujemy w aplikacji listę szkół. Wyświetlamy je 5 per strona. Aby pobrać dane, które zostaną wyświetlone na stronie trzeciej, należy wykonać takie zapytanie:
**GET /schools?offset=10&limit=5**

### Sprzątanie bazy danych

Aplikacja dysponuje komendą konsoli Symfony, która czyści bazę danych z niepotrzebnych informacji. Usuwa ona:

- Przedawnione tokeny uwierzytelniające użytkowników (te, które otrzymuje się po zalogowaniu i które trzeba przekazywać w nagłówku zapytania pod kluczem X-AUTH-TOKEN). Usuwane są wtedy, gdy ich czas ważności minie.
- Nieaktywne konta subskrybentów - subskrybent jest uważany za nieaktywnego, jeżeli do tej pory nie aktywował swojego konta (poprzez potwierdzenie rejestracji), a został zarejestrowany już jakiś czas temu (domyślnie jest to doba).
- Nieprzypisane konta szkół - nieprzypisane do żadnego subskrybenta szkoły są usuwane, chyba że minimalny czas od ich utworzenia (domyślnie doba) również nie minął, albo konto ma status accepted (czyli zostało utworzone lub zatwierdzone przez administratora i przystosowane do wyświetlania użytkownikom na liście do wyboru).

Aby uruchomić komendę sprzątającą bazę danych, należy posłużyć się poleceniem (dla przykładu, uruchamiamy komendę z głównego katalogu projektu):

```bash
$ php bin/console app:clean-database
```

Można też podać dodatkowy parametr, który określi limit wieku zasobów - tzn. po jakim czasie ich istnienia, będą przeznaczone do usunięcia. Określa się go w sekundach. Domyślnie jest to 86400 sekund, czyli doba. Aby użytkownicy byli usuwani po dwóch dobach, można skorzystać z polecenia:

```bash
$ php bin/console app:clean-database 172800
```

