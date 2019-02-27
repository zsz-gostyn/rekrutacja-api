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
  | active     | Określa, czy post jest aktywny; jeżeli nie jest aktywny, nie będzie prezentowany użytkownikom | Wartość logiczna               | Nie (w przypadku niepodania wartość fałszywa, w przeciwnym wypadku - prawdziwa) |

- schools - zasób ten gromadzi informacje na temat szkoły, w której aktualnie uczy się subskrybent. Szkołę opisują następujące pola:

  | Nazwa pola | Opis pola    | Typ                            | Wymagane |
  | ---------- | ------------ | ------------------------------ | -------- |
  | name       | Nazwa szkoły | Tekst (maksymalnie 255 znaków) | Tak      |

- subscribers - zasób ten przechowuje informacje na temat danego subskrybenta. Subskrybentem jest użytkownik, który wyraził chęć informowania go o przebiegu rekrutacji i zgodził się podać swoje dane.

  | Nazwa pola | Opis pola                                                    | Typ                            | Wymagane |
  | ---------- | ------------------------------------------------------------ | ------------------------------ | -------- |
  | first_name | Imię                                                         | Tekst (maksymalnie 255 znaków) | Tak      |
  | surname    | Nazwisko                                                     | Tekst (maksymalnie 255 znaków) | Tak      |
  | email      | E-mail                                                       | E-mail                         | Tak      |
  | school     | Identyfikator szkoły; identyfikator zostanie zwrócony podczas dodania szkoły | Identyfikator liczbowy         | Tak      |

Każdy zasób posiada dodatkowo pole **id**, które służy do jego identyfikacji.

### Akcje

Na wyżej wymienionych zasobach wykonuje się akcje. Można przykładowo: dodać nowy, zedytować istniejący zasób, usunąć go, albo odczytać informacje jego temat. Rodzaje akcji i uprawnienia potrzebne do ich wykonania, zależą już wyłącznie od rodzaju zasobu. Lista pól, które należy przesłać podczas poszczególnych akcji, znajduje się wyżej, w sekcji *Zasoby*. Poniżej znajduje się lista akcji, które można wykonać na poszczególnych zasobach:

- posts

  | Metoda HTTP | Ścieżka     | Opis                                                         | Wymagane uprawnienia                         |
  | ----------- | ----------- | ------------------------------------------------------------ | -------------------------------------------- |
  | GET         | /posts      | Pobiera listę wszystkich postów. Domyślnie wypisywane są dwa. Aby to zmienić, należy zapoznać się z sekcją *Stronnicowanie*. Liczba wszystkich istniejących postów znajduje się w polu **count** odpowiedzi. Natomiast dane prezentujące poszczególne posty znajdują się w polu **data** odpowiedzi. Administratorom zwracana jest lista wszystkich postów (zarówno tych aktywnych, jak i nieaktywnych), natomiast użytkownicy anonimowi otrzymują dostęp wyłącznie do postów aktywnych. | Każdy może wykonać tę operację               |
  | GET         | /posts/{id} | Pobiera informacje na temat konkretnego posta (w miejsce {id} należy wstawić liczbowy identyfikator posta). | Każdy może wykonać tę operację               |
  | POST        | /posts      | Dodaje nowy post. Wymagane do wysłania pola opisane zostały wcześniej, w sekcji *Zasoby*. | Tylko administrator może wykonać tę operację |
  | PUT         | /posts/{id} | Aktualizuje post o konkretnym id (w miejsce {id} należy wstawić liczbowy identyfikator posta). Wymagane jest wysłanie wszystkich obowiązkowych pól (zostały opisane wcześniej, w sekcji *Zasoby*). | Tylko administrator może wykonać tę operację |
  | DELETE      | /posts/{id} | Usuwa post o konkretnym id (w miejsce {id} należy wstawić liczbowy identyfikator posta). Nie trzeba wysyłać żadnych pól. | Tylko administrator może wykonać tę operację |

- schools

  | Metoda HTTP | Ścieżka       | Opis                                                         | Wymagane uprawnienia                         |
  | ----------- | ------------- | ------------------------------------------------------------ | -------------------------------------------- |
  | GET         | /schools      | Pobiera informacje na temat wszystkich szkół. Domyślnie wypisywane są dwie. Aby to zmienić, należy zapoznać się z sekcją *Stronnicowanie*. Liczba wszystkich istniejących szkół znajduje się w polu **count** odpowiedzi. Natomiast dane prezentujące poszczególne szkoły znajdują się w polu **data** odpowiedzi. Administratorom zwracana jest pełna lista szkół, natomiast użytkownikom anonimowych tylko lista szkół zatwierdzonych (zaakceptowanych). | Każdy może wykonać tę operację               |
  | GET         | /schools/{id} | Pobiera informacje na temat konkretnej szkoły (w miejsce {id} należy wstawić liczbowy identyfikator szkoły). | Każdy może wykonać tę operację               |
  | POST        | /schools      | Dodaje nową szkołę. Wymagane jest wysłanie wszystkich obowiązkowych pól (zostały one opisane w sekcji *Zasoby*). Szkoła dodawana przez administratora jest domyślnie zaakceptowana, natomiast taka dodawana przez użytkownika nie jest domyślnie zaakceptowana - oczekuje na zatwierdzenie przez administratora. | Każdy może wykonać tę operację               |
  | PUT         | /schools/{id} | Aktualizuje konkretną szkołę (w miejsce {id} należy wstawić liczbowy identyfikator szkoły). | Tylko administrator może wykonać tę operację |
  | DELETE      | /schools/{id} | Usuwa konkretną szkołę (w miejsce {id} należy wstawić liczbowy identyfikator szkoły). | Tylko administrator może wyknać tę operację. |

- subscribers

  | Metoda HTTP | Ścieżka           | Opis                                                         | Wymagane uprawnienia                         |
  | ----------- | ----------------- | ------------------------------------------------------------ | -------------------------------------------- |
  | GET         | /subscribers      | Pobiera informacje na temat wszystkich subskrybentów. Domyślnie wypisywane są dwaj subskrybenci. Aby to zmienić, należy zapoznać się z sekcją *Stronnicowanie*. Liczba wszystkich istniejących postów znajduje się w polu **count** odpowiedzi. Natomiast dane prezentujące poszczególnych subskrybentów znajdują się w polu **data** odpowiedzi. | Tylko administrator może wykonać tę operację |
  | GET         | /subscribers/{id} | Pobiera informacje na temat konkretnego subskrybenta (w miejsce {id} należy wstawić liczbowy identyfikator szkoły). | Tylko administrator może wykonać tę operację |
  | POST        | /subscribers      | Dodaje nowego subskrybenta. Lista wymaganych pól znajduje się w sekcji *Zasoby*. | Każdy może wykonać tę operację               |

### Stronnicowanie

Stronnicowanie jest stosowane w przypadku akcji zwracających listę wielu zasobów (na przykład listę wszystkich szkół). Zapobiega ono przeciążeniu serwera. Domyślnie zwracane są dwa pierwsze rekordy. Aby to zmienić, należy posłużyć się parametrami adresu URL. Są to:

- offset - przesunięcie, określa który rekord zostanie wypisany jako pierwszy. Domyślnie jest to 0
- limit - liczba zwracanych rekordów. Domyślnie jest to 2

Przykład: przygotowujemy w aplikacji listę szkół. Wyświetlamy je 5 per strona. Aby pobrać dane, które zostaną wyświetlone na stronie trzeciej, należy wykonać takie zapytanie:
**GET /schools?offset=10&limit=5**

