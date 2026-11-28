import 'partes_date_format.dart' show parseIsoDate, todayIsoDate;

export 'partes_date_format.dart' show parseIsoDate, todayIsoDate;

String formatMinutosAsHhMm(int minutos) {
  final safe = minutos < 0 ? 0 : minutos;
  final h = safe ~/ 60;
  final m = safe % 60;
  return '${h.toString().padLeft(2, '0')}:${m.toString().padLeft(2, '0')}';
}

bool isValidDuracionMinutos(int duracion, int tramoMinutos) {
  final tramo = tramoMinutos > 0 ? tramoMinutos : 15;
  return duracion > 0 &&
      duracion <= 1440 &&
      duracion % tramo == 0;
}

List<int> buildTramoOptions(int tramoMinutos, {int maxMinutos = 1440}) {
  final tramo = tramoMinutos > 0 ? tramoMinutos : 15;
  final options = <int>[];
  for (var value = tramo; value <= maxMinutos; value += tramo) {
    options.add(value);
  }
  return options;
}

bool isFechaFuturaIso(String fechaIso) {
  final parsed = parseIsoDate(fechaIso);
  if (parsed == null) return false;
  final today = parseIsoDate(todayIsoDate());
  if (today == null) return false;
  final day = DateTime(parsed.year, parsed.month, parsed.day);
  final todayDay = DateTime(today.year, today.month, today.day);
  return day.isAfter(todayDay);
}