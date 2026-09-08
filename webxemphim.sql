-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 08, 2026 at 08:03 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `webxemphim`
--

-- --------------------------------------------------------

--
-- Table structure for table `movies`
--

CREATE TABLE `movies` (
  `id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `genre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` decimal(3,1) NOT NULL DEFAULT '0.0',
  `year` int NOT NULL,
  `duration` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `poster` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tagline` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `director` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `cast` json NOT NULL,
  `video_url` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `trailer_url` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `movies`
--

INSERT INTO `movies` (`id`, `title`, `genre`, `rating`, `year`, `duration`, `poster`, `tagline`, `description`, `director`, `cast`, `video_url`, `trailer_url`, `featured`, `status`, `created_at`, `updated_at`) VALUES
(1, 'IRON PROTOCOL: ĐIỂM KỲ DỊ', 'Hành Động / Khoa Học Viễn Tưởng', 9.2, 2026, '148 phút', 'https://images.unsplash.com/photo-1635776062127-d379bfcba9f8?q=80&w=1600&auto=format&fit=crop', 'Khi trí tuệ nhân tạo vượt khỏi tầm kiểm soát, chỉ một bộ giáp có thể ngăn ngày tận thế.', 'Khi một trí tuệ nhân tạo vượt khỏi tầm kiểm soát và bắt đầu tiếp quản hệ thống phòng thủ toàn cầu, một kỹ sư thiên tài phải khoác lên mình bộ giáp cuối cùng để ngăn chặn ngày tận thế trước khi quá muộn.', 'A. Stark', '[\"T. Rogers\", \"N. Romanoff\", \"B. Banner\", \"W. Maximoff\"]', 'https://www.w3schools.com/html/mov_bbb.mp4', 'https://www.w3schools.com/html/mov_bbb.mp4', 1, 1, '2026-08-30 17:42:12', '2026-08-30 17:42:12'),
(2, 'MẠNG LƯỚI BÓNG TỐI', 'Hành Động', 8.7, 2025, '132 phút', 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAUFBQUFBQUGBgUICAcICAsKCQkKCxEMDQwNDBEaEBMQEBMQGhcbFhUWGxcpIBwcICkvJyUnLzkzMzlHREddXX0BBQUFBQUFBQYGBQgIBwgICwoJCQoLEQwNDA0MERoQExAQExAaFxsWFRYbFykgHBwgKS8nJScvOTMzOUdER11dff/CABEIAJQAlAMBIgACEQEDEQH/xAAxAAACAwEBAAAAAAAAAAAAAAADBAACBQYBAQADAQEBAAAAAAAAAAAAAAABAgMEAAX/2gAMAwEAAhADEAAAApLzXlsWpFa1CZcbVtZRKT1lebk6DkurdNX2lszcrn66GuYL+HvMU2Y8VvLxesSt0auJ0Wdl2ZDAFp11YlXj6XOs8+/Jl3Q4yu4vqkrqovaMBolFkWZCeL1uiryxY23ebbnMVR8DqMXS5ytmVPWshdItnTttUxveXWtjM3zsSRDhOdLGTIccJSWUntEz6cE6TcrqC1AOKq32CE3tZZBzldNHUgVDCrOs25npuL5egqFJc1YoL6ifdn5u/iZ9eZ6zKljqOf6VFzluka5cQHR+UTmadTz83SlJn1B2uS1GTsrIu1y38ntlS4rvEJV4idiNmz9pdgjSvPDGVEsrH4vaQWuPOzi95kbeZNr6/G9Ky7PtL1jFmB8UffCiwL+tuhhxcoBBY6UbYu0EtPIF54CLE9AXLnI2yhs0S+SME/LhWxHAeUn7m6WCTj7ufoxpoVpI9eDlByZRlY+EtYd0xUmghZf1lCFpZmKv44wVwtTIcpFEwnHLUs3BLQdmGkPWtIp1tWTprOyHqGkdapyPw+dkdwsyJ1vJEa8k7v/EAAL/2gAMAwEAAgADAAAAIS7NlS8W8IAnF0KMTZajDBbWvYE6aqDxu+E6cu5ifFI2Ya7KpC/JOXPS9JzmAyX/AKkj9jdjObzGA6fLzf56d5KR9uOjCCMEP5yB94H/xAApEAACAgIBBAIBBQADAAAAAAABAgADBBESBRATISIxFCAjMkFCBjRD/9oACAEBAAEHAtQQdhLLBUhZAzE2vbqHIMGRBbOlorUtYe2eW/Kth5QVMZ41E2ncQdslvJaK7MngNLVZcZ+FqPhvNNUZ0a6t8ZlY9sm1RbZGuE8haJRsiDDQDuII54qSQx2U9/JG1EdbI92jxbyfeJktiXra1yfGCxZkqWvtJqaYSqfLFr4zw3HtqDt+L5lI6lZRTQaBW5WOrMYtSgA2VIF3p43kCg4xqyKKy3LcKfxly6V5hOtXlJzH9hrWc74/c4zy0VQ51YmT1E8deRrb5p7W8XAaLpVZZHDgkPQta1s6sgsTGybKDK88OkXJQ+3uR0IVfHs/W45vDR+oN9PlWNOZnI6i6YgPjpU4PyQB15n4YK8HEzl/dJJY6D+9yunDqprNhXZgac4toEXKMGV2rw8i2V9Lg6djS3p6j3+PYjgJZ9g/tVM6dUy/puoZBbTBSis0/CztLKulDRl9S12MiY7Wx8O9YVZTrm0OLRj8ew7XnQU8VyK+JIZmKNyqZFx6WEuxq1nIsAvTMWtt3FtDb362RSlptuy72R9HKvUbbJts9YvTKL6KrLirqjQdsgfCVWFDM9Kw6WWN49lr2MW/XqivyusUGhBXfaxETAzbeJXp1gRFt6Ir6Nf/AB75Et0vFpucNg1sdnB4ymm+ytSmDdPw7QI2PY21TBtZtdQxQmKYNOBDjjkSKFU7wRyy6UPE8DTTUF3r9GbZxyLIciLkTpVvI2rvUHfIqRw6X4+Riu0N4ivZb66diPS3mKc14qNDuWAG7LwnrNyTbk3HkZynSsjx3tFyA2oD3uXmhHsiWY6uJ4wgin3KW+S9y/8AM3WBTp2Z1Yjp2MsTpPT+CyynHaW4a02eUH3rHfkiN2MYcdn+bGFv6esBd0L8h2sOhLLQvGfPI8kODkWBYnTqBNTgFG8rIczlYbC+DbyTQ+u9o2pgX+gOIj+5Wuve/nMltcRkXe2nSeaV2z8riZtDNAy9vydSzGyrRpum5A1MDy1ZHFD8f0fW439RRyeE8RtJmWgG1jcrbnS/4WRj9hX8cOeBK8piIL6zEtcRcu3ek/sK032cfKfZ3T/cub2qcuKM+VayV8kzTKc2qHIqMFtc5rKvWxuDR1PLcu5j2c6caz/RXZitHB4mBG9TklKyvlY5bLf+Nef/AOYE3qVtyG9zc1p4FgAhAmB88NRZ9K6MHWFJwUe3vJ9LS9h2ONKE+22/USfPoEmKgMrpENNk8Vk/0O5+p0cnxsB/sBipWByRFQP8uIj+tTNJCoGH7ta9Q/7jRfQ2h2OwJE89wn//xAAC/9oADAMBAAIAAwAAABATBiAyyTZgKA8DLjSxDzIS9hxDfzTg+ywATyjqR7DBnxbTxRC81wShRhTCFkDxtgyCBRwQxxh0EUTUCcHzh3lVGiDzCBwH970ByAL/xAAuEQACAgEDAQUGBwAAAAAAAAACAwAEEgUTIgEQESMyMxQVJEJDUzRUYWOCkpP/2gAIAQIBAT8AIplNSZumusMK8NEdoVz320j9SWtRGzTx+p2V0vJSsa8q6Uxvr2MJ7Bo/6/3mUMwX5+sZq6N+zt+IZwsWlyZBSGc2xGLdV6nifqSpcyJSpuPEPDXPjf2p771Q+IrUuWQsPPJ1lpymKk7sU5QgyJuCT8ZuBGCi0fpysTazclNg67qylY+Ez+E9/wB/8sqU6qrKltg00D9OarX2G5B6Zw148osq2X4aD4Y5QlG5fcTf8+E0inWsqyLyRujV+oZLswlJ6EXSabqiFI2FeJBvZfTlrrWsoYJHFj8s70YYywOQ8YumRDyi86wba2TVbFoa/hN5z42LTsHkEr2sjxKDgUd4b2DF7RRi8p5AjCKWCWvbFk31SuWRYxi9ospVdxlrFnIYJRJZDGco5ykDlN5TeXqTvH7UESRCssPzSuzHjCcJRcTxGfJlLhNavHbi91R5T24/tQpw7Nki5DBWQ+aLhDwxhDj5ozHOYjGdhSt164xPpRPnhSxB7P/EACsRAAIBAgQFAwQDAAAAAAAAAAIDAAQSAQUTMhAUIiMzERZCBiFDUxVRZP/aAAgBAwEBPwAeGVJENR5QaUqk7tSfx1oQqclFLSsjBG7ySqrqakG6e6f80+cAMTlLRFyyxi7lD0rlx2eOMIj3LhU7x/HHq+Urmd+a4f1ByLLhK4mNZKTCiQNq6aP1HD0eOGlpEqMSYjNE90XdTh19yVWg8fFGfT2WVJ3lqrntfJv2SszRtM1ihXCzKsL8kyiuJqrGt7kQ66MGpEfLDdNdpl0ytc9ZW/ODVVA+WakzPJmk/Xb24WXYDtbKcKmmbcIRRXBLGwt05/TLojKg3ncUyoRfVWN2TlqX9crC5lDFxiTVu4UZXLhMbth7eAJuKU46X3Gc6MKM74QxlMyw+FQVoRIXGuaZCNwRhPHdw6SgrEdsaNxMLCWwNmEqLmPtiR67oJDNMGjaU5NUHhb6liMuEfUSwi2kXQMZYvb5IA2hBgy4ovgXllVhh2/tG44xe5c+EXx//8QAMRAAAQMCBAUCBAYDAAAAAAAAAQACEQMhEBIxUSBBYXGBE5EEIjJCMFKSobHRYuHw/9oACAEBAAg/AuHZPEuKmFCjDL82aOI8fJuqDRK59UH3QeDgD84MkYxzVkLpzvAXoj2/vh2XdxwdTfH6Uwk9nX9ii4z+XNfysru5/wBJumjgswupUfcVCd0TWG6PC8w0qlq6J8IWA5o1Fnug/wCYr1E6/VB18oRK6LqU5s2CbARL8LJ9QJgTE5Aw0Alx5BU6mdo1tH9poJR5KrUgv5Dl3R5JrlUCnsp1lZgryhRMIIuwnAEHN+yZqdU1jWjoEAoTrxgaILy0SU2w2UnHMpwFOOpsn1fZFpPlUjHQqqIEWOq5JglydbrCaNURBhBGn4XxDe19EGxlshbqsk9kRBUqlTA4OqOouE0QMxW6LoTHZitlUb9LhkwcbJxJl1kzS6FR0J7yU8uDnDdNcCNxxMEepOYdRgE6UPuIA8r04DUwTyaN062v1LMLL1qgteIVX4pzugEe6a3TT/isxVGs5vRZZOhRICzArKjZTLmvF1tqhbwibraT7J8cgmjfh7Y9jwnSojTMbjREJlMnsqv1chsufEDbNju1d+Ei6cgLYdcdlyZcrNDnaHZP+Ic7sF6APdOosPhUhEcluunCdAgFz4NvnKbBgy/uV6raY7Zk4uf+38YHRNpkUx9xGqpV3F20WPutLzH4O2E3Qg/TMqqzJ/lqFOAr5APtyym16ThtpK9I+Lp1N4a7cO4euG2HMrk0Qs4utzKN0xuYbT/C9F6dS/Sf7WaO4TKkjoZToXnh9sN9V7LmSnU2nsoIUrMswxhZyehuvzNErfRH34TqUPPHvwbIYucqbU/TA6uNls0YTCFVqiVl4epXJDB11GHJcg0LpwAwvUK//8QAJxABAAIBAwQCAwEBAQEAAAAAAQARITFBUWFxgZGhsRDB8NHhIPH/2gAIAQEAAT8hgQwIIw9nt2JtFDwG0e96lzZJz2i71+YjVEfOoDwBtN2Pw+lxZ9Tos1LH3N2/Up2lQIYJoTK2btv/AJHLGNNg2mqcmLFpwrI+YDhupmOUfWSZQj7ow9pS+SDN69uCX9R8y6nq0na1y11YcBOuf2lSvyGxsWWobJ2rqxZot21YqH0J+UWINWO8eSo9yE2H7T6CiXLhdBK7xW5ceQMzWw6y8nVzDVe/4Jlf26uGpB1RqW7HB2q9u01WD/csr8pFIPEq6/UJjPzNZZYdu6q1SiWE26Waw9pqdHmo34ftIVjo3dmbgJmHNZmVdv0nD5QH1ononqznMTSz6nbITPIi7oYt7TTajyRVw+Zvb1qiUoZT1G4S5mLbCKAFq1l7Gsc+wGZFl4Lw6xoHHjLBYbTje4zHJAtHNk9Ru7v46JBtlcbXDurUN/0YJqYvo1ZrBujCPxEvll0NKYsHclP9ltXbxLm2pl187wpWtIEDrDPPcLM9sxpgsTeeRm57txOOqsPjWsXfr/f3K0ZsXmnpLFOtTcBQGUl6Lb1eppUH9pE5EveaK1+PwEfcqrx2XzmH+g/thyu5M+qhZaZLFPDqRfNE4hZnFazVdVd7lMMkzDpeitdI8uCzZdyxkDpMqpecc2wS4zpYvzxLxe0NHlvdVMgDVlbp1zpGu5lpLf5A/GsZK6mGUaQ+cK51b5Vz+RGK/lQMC14XrmNADA64agq8e9QCX9t4W9UcnxFG2V6mdD2AWtb9IKdWJtwNdbjYHH0LX5hccs6NrrwdYSK7rphtFu8y2qBAm+Iv9WCCV3PyIqWtEhvNZxPDpByeYZS6rbWUOZ1csZAZcRga7lzGJJVdlPXzCFtmsWp3A+Z7+1ul4LzOQocby2dJr3alu0IBy6Kx3XmUBymWXTm0Wr2yo+VIX4j9ycLZfMReMvlmhZOmmO8yrya7e4lHVnd/yNos1dTSXVMhIWjTPL4maTZtCCav5RiZJ1aBlvrCT6XXnVyZ2gDQ/wDB/wBmkG9Z1Zn/AGPvoxKp4X1Fft/FdJxknkMTX6tFUcj+mWNJ9fDFNXbF+oySqproeesIrZCedR8ShOnz+LjNMQVrj5mwMDsYi28OqUa9X4Ye1yI/yEh+Q9B3heGjY4vpdyiEhaY0+4yVbs/hlJsYHeCG4zruxMknbMlsC9offh/5bhLMXVqxlsb3L7YVsFdNK0QimbH0njq9zDL/ABlCgxWfRLf/AIIgg5eb3HFSj7yt8HwaEKgtyg6HiaipnXTgyBBS+OX6VgAAMEtFoHYlycbJ9tA+Y4OUgrxCKglhT2Ark8TMdvxcwriPT1S2ZYS9aNqbD7njCxsOTfcDTzBkLdN8oc7TSINAPtdokujjvHMzCzjtVC8qOYnTimyhtYk3/hm4PsagMBXRF1ilsmL0/Loyrd6LA5ZWkAq0CXQXVWMV/QTmB6LS5dZm1OHI5PU0QZadOtx78xsBv/xFqbehBaXq+LTez6weyyCCnYnoYYIyy8U17iB5j0Zpj/dPwueBvBv2mI1vrLh7r7CN70CEQcXXPVh0OrCmWC/kTGSjQRf/AEjp1pSBF/h9zGVDSkfM6ALuSGudj3RHGD6PeWucf2sQvMrbJdK28a5n/KCTsXP6S1d5/R+BzSmttU749cz62/JCzOFMDG0YS7g8OIl1ghJlujUo5Qcy48j/AAgxPswbTC2PRK7DWCJ0FNtlbfqUhbt40uvuU/opjfrn2sIfgzlg++C+MxqWsusywrhcQEwIrE3YYXJhFTQDvEuWFja3+5kXn8Zll0ZoCP8A/8QAJxABAAICAQMDBAMBAAAAAAAAAQARITFBUWHwEHGxgZGh0cHh8SD/2gAIAQEAAT8QYyzD67rn0SEK5GMQHZWd5dwmGWLtN3wbQb6QojcvFQMaIGID4fxIU1YvaKfJBzdjQ9DLMPprmZ8lAS4SilLG7e3EwXYiZup5RYKWcqQKToXCgxxC2EThJVgLBXvDbrqBhMxm7MgLmdT/ADsmc3hhmccimKN1fCB7JrNURYzquBBrnMD/AO3B9IGNoL/gbY1BnoDx7lpjhFPefhMwg5s0qE6Go1uCKUBjWoK0/wDyQbQAuWAV5k4v+6awygm8BwncwP8AhSu2YQO4ESy7ojGujM1BajkwxQ+lQCZBi/KFPfyINwFK3JcpixS2FTPpx4gqWGlAtYIBrBOCMh5SxSneFAtcIEtD12Jhwz2WHf59FNyiwAXyhpa/uETXuMyIcOQvSP7EbPsBhcKOqrHKCipzvlFfQeXMv0hoQ1JGYTKW3rEoYx1VZLCPCLVoxSD0F4Ste1wbBXLYF1S2XzXiY8L3+jxiBZwBLoK16SpAhsNubLDq9zmBhosriG518uY9eBCEBbuTCKLd0I47sJkrnaCbbe7OKfaDmRUl8b+CDznlPee4xzOcwgEYeGk/AQAr8ZgSxqXDYew+BoUK4ZxBa+QhYmTQ1lHI8xb5z8pVw/oHA3RNwqVQ4bEAWM5kBAxsEAlJUsf92/8AIkTnmGoJweZxnpUjAASIaklk+pQwgAum7CMZa4Nllmavqi+h31IQ8McvWU6ekFyjxZnVYQ8RhdX8Qhb04YV8R2IlNht2hb3pz/oenf6ATtoU4iz6QazwWlf4GZSWhJHDcSLjRYecw6xg8MCfg2ZeYyAsBlcGlL+EEC4XvNpBfcay9ARkun6DoBdM4wsIL0LUFklmjMKDDc8gu4rRaDNkySUQE4T1YeaSDsXQiSv5AperbIRXz9rFA0inV/KMWA3HwwhIjlLD6QtxAqAO3/Bt4xlpjASrzORPyCA3/Iv3Kt+gHe3NS10G70/WDDlGiRwB1+vwWFLS7GDyMBfAmIIdEDr5hDqwrKvu9E3XMxoA+8WDEsEZ7rDfFiLXf7yosudmbHal9KGfEtxXn29DUU7S7HHBhpKYSVDpkAe44utxHE1h35spgion7nP/AGkyX6Tz+YixTB8KDMxDO9mA/US3fL3HlAMH3MgK0tmHx3mQN+POkMbg2QUqOJdRUruJjT7CxxviF7DyEdOpZaOsNuWAuv7yS5dxtD+MKg+BWLCAG7Bd04B4UUQJ+6VaIMjtrf8AQHWBeLqguEBWMy1mtz9qFkVzrBFXKbhcLBQbMPC3MDy6IuBXibxtvvFMXLLUVM6Yo1eJEZcMv1yC8GyB0PLDpFLb+5GjbiLwLg698UAl+NmvpAh0MQ+0BGXrODBwJHMFgwr3RLK3pS08mTMUl5nLpvntUNTjl15WEBZpUIEGmidVKvFQKJMKwD0NCKDmrRdL4XxD3iWeEPTAUaMKehr0YWLKqRBMdcD3jBjOobZn296H3c87MVIuKIQySRYyhalNyFNVcUkLcXcnytiVxWFsnJTzBiR7YIxPL5EV1z4BckJhhZdIzGnoRYOy1TBvwwwAT16RSldJyi+PHqdRpxEOK2kDWWUrizMskaGIW4egcpFCnPSFb5bmahnve0/uTCZli4payMr0Z4oj0EW+JakmrkGSnQtke1F8jPP88vYnDvB+saqrBqVusBFM4SDWKuqgZfDNTN/Wf5sLCS9CSsSimIFV9iWtrYwH0hSl7EfAChlabJPaHH9ssttStIdzvCVhcOot3MYnAKf/2Q==', 'Một đặc vụ ngầm phát hiện âm mưu thao túng dữ liệu toàn cầu.', 'Một đặc vụ ngầm phát hiện âm mưu thao túng dữ liệu toàn cầu và phải tự mình phá vỡ mạng lưới trước khi nó sụp đổ toàn bộ hệ thống tài chính thế giới.', 'K. Danvers', '[\"J. Drew\", \"M. Okoye\", \"S. Wilson\"]', 'https://www.w3schools.com/html/movie.mp4', 'https://www.w3schools.com/html/movie.mp4', 0, 1, '2026-08-30 17:42:12', '2026-09-07 12:53:56'),
(3, 'HÀNH TINH SONG SONG', 'Khoa Học Viễn Tưởng', 9.0, 2026, '156 phút', 'https://images.unsplash.com/photo-1462331940025-496dfbfc7564?q=80&w=800&auto=format&fit=crop', 'Một nhóm nhà khoa học phát hiện cánh cổng dẫn đến hành tinh song song.', 'Một nhóm nhà khoa học phát hiện cánh cổng dẫn đến hành tinh song song, nơi mọi quyết định của nhân loại đều rẽ theo một nhánh khác.', 'P. Quill', '[\"G. Danvers\", \"R. Raccoon\", \"D. Groot\"]', 'https://www.w3schools.com/html/mov_bbb.mp4', 'https://www.w3schools.com/html/mov_bbb.mp4', 0, 1, '2026-08-30 17:42:12', '2026-08-30 17:42:12'),
(4, 'CĂN PHÒNG SỐ 7', 'Kinh Dị', 7.9, 2024, '104 phút', 'https://images.unsplash.com/photo-1509281373149-e957c6296406?q=80&w=800&auto=format&fit=crop', 'Một căn phòng bị niêm phong trong tòa nhà bỏ hoang.', 'Một căn phòng bị niêm phong trong tòa nhà bỏ hoang ẩn chứa bí mật khiến bất kỳ ai bước vào cũng không thể quay lại như cũ.', 'W. Maximoff', '[\"V. Vision\", \"A. Fietro\"]', 'https://www.w3schools.com/html/movie.mp4', 'https://www.w3schools.com/html/movie.mp4', 0, 1, '2026-08-30 17:42:12', '2026-08-30 17:42:12'),
(5, 'ROBOT NỔI LOẠN', 'Hoạt Hình', 8.3, 2025, '98 phút', 'https://images.unsplash.com/photo-1485827404703-89b55fcc595e?q=80&w=800&auto=format&fit=crop', 'Một chú robot gia dụng nhỏ bé phát hiện khả năng đặc biệt.', 'Một chú robot gia dụng nhỏ bé phát hiện khả năng đặc biệt và dẫn dắt cuộc nổi dậy để bảo vệ những người bạn máy móc của mình.', 'H. Hogan', '[\"F. Foster\", \"E. Ross\"]', 'https://www.w3schools.com/html/mov_bbb.mp4', 'https://www.w3schools.com/html/mov_bbb.mp4', 0, 1, '2026-08-30 17:42:12', '2026-08-30 17:42:12'),
(7, 'Alibaba và 7 chú lùn', 'Hoạt Hình', 8.7, 2026, '360 phút', 'https://m.media-amazon.com/images/I/61k17jc7lxL.jpg', 'abcxyz', '123456', 'Nguyễn Nhật Tèo', '[\"Tom Holland\", \"Ronaldo\", \"Messi\"]', 'https://www.w3schools.com/html/mov_bbb.mp4', 'https://www.w3schools.com/html/mov_bbb.mp4', 1, 1, '2026-09-05 07:26:25', '2026-09-05 07:27:45'),
(8, 'Bạch tuyết và 40 tên cướp', 'Kinh Dị', 10.0, 2026, '135 phút', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTTCvvgvYMsoFnaGNXbSsOrkWWL585D50Qev2QXIA09yA&s=10', 'Hành trình của bạch tuyết', 'Siuuuuuuuu', 'Bạn học Võ', '[\"Thanh Bel\", \"TanL\"]', 'https://drive.google.com/file/d/1r_eNkkwkibMyJ39AdeH4FRCxB_zGtHPj/preview', 'https://drive.google.com/file/d/1r_eNkkwkibMyJ39AdeH4FRCxB_zGtHPj/preview', 0, 1, '2026-09-08 05:45:04', '2026-09-08 05:45:04');

-- --------------------------------------------------------

--
-- Table structure for table `movie_comments`
--

CREATE TABLE `movie_comments` (
  `id` int NOT NULL,
  `movie_id` int NOT NULL,
  `user_id` int NOT NULL,
  `rating` tinyint NOT NULL DEFAULT '5',
  `comment` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `movie_comments`
--

INSERT INTO `movie_comments` (`id`, `movie_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(2, 5, 3, 5, 'Phim hay như cách CR7 có world cup', '2026-09-05 07:04:44'),
(3, 7, 2, 5, 'Đùa phim ơi', '2026-09-05 07:30:13');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('member','admin') COLLATE utf8mb4_unicode_ci DEFAULT 'member',
  `status` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `username`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin@gmail.com', 'admin', '$2y$10$sZ4ix8Mlbeva7XYVKF2lceKaOvzoLbenQkds1Ip09iD0/lMYw2gCK', 'admin', 1, '2026-08-30 17:42:12', '2026-08-30 17:42:12'),
(2, 'phong@gmail.com', 'TanL', '$2y$10$OM5OmRzDc5bbXO6q0wbx8.eXL88rwabMFs9LbWP2FUWwlMcAZXF9e', 'member', 1, '2026-08-30 17:44:42', '2026-08-30 17:44:42'),
(3, 'thanh@gmail.com', 'ThanhL', '$2y$10$LtoZgFOwZlOhJ.bfkHy0c.HKfZN3MaujrXMaLXsRLgFYVs5.uiJ6m', 'member', 1, '2026-09-05 07:03:15', '2026-09-05 07:03:15');

-- --------------------------------------------------------

--
-- Table structure for table `watch_history`
--

CREATE TABLE `watch_history` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `movie_id` int NOT NULL,
  `watched_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `watch_history`
--

INSERT INTO `watch_history` (`id`, `user_id`, `movie_id`, `watched_at`) VALUES
(1, 1, 5, '2026-09-08 05:47:22'),
(2, 1, 7, '2026-09-08 05:47:01'),
(4, 2, 7, '2026-09-08 05:49:34'),
(7, 2, 5, '2026-09-07 12:50:46'),
(10, 1, 2, '2026-09-07 12:58:01'),
(14, 1, 4, '2026-09-08 05:33:14'),
(20, 1, 8, '2026-09-08 05:45:20'),
(25, 2, 8, '2026-09-08 06:50:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `movies`
--
ALTER TABLE `movies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `movie_comments`
--
ALTER TABLE `movie_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `movie_id` (`movie_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `watch_history`
--
ALTER TABLE `watch_history`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_movie` (`user_id`,`movie_id`),
  ADD KEY `idx_history_user_time` (`user_id`,`watched_at`),
  ADD KEY `fk_history_movie` (`movie_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `movies`
--
ALTER TABLE `movies`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `movie_comments`
--
ALTER TABLE `movie_comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `watch_history`
--
ALTER TABLE `watch_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `watch_history`
--
ALTER TABLE `watch_history`
  ADD CONSTRAINT `fk_history_movie` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_history_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
