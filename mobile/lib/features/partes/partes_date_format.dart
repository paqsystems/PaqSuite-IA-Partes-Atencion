/// Fechas Partes: API siempre ISO `yyyy-MM-dd`; UI según locale (es → dd/MM/yyyy).
class PartesMonthRange {
  const PartesMonthRange({required this.fechaDesde, required this.fechaHasta});

  final String fechaDesde;
  final String fechaHasta;
}

PartesMonthRange currentCalendarMonthRangeIso([DateTime? reference]) {
  final now = reference ?? DateTime.now();
  final first = DateTime(now.year, now.month, 1);
  final last = DateTime(now.year, now.month + 1, 0);
  return PartesMonthRange(
    fechaDesde: isoFromDateTime(first),
    fechaHasta: isoFromDateTime(last),
  );
}

String todayIsoDate() {
  return isoFromDateTime(DateTime.now());
}

String isoFromDateTime(DateTime value) {
  final y = value.year.toString().padLeft(4, '0');
  final m = value.month.toString().padLeft(2, '0');
  final d = value.day.toString().padLeft(2, '0');
  return '$y-$m-$d';
}

DateTime? parseIsoDate(String iso) {
  final parts = iso.trim().split('-');
  if (parts.length != 3) return null;
  final y = int.tryParse(parts[0]);
  final m = int.tryParse(parts[1]);
  final d = int.tryParse(parts[2]);
  if (y == null || m == null || d == null) return null;
  return DateTime(y, m, d);
}

String formatIsoDateForDisplay(
  String iso, {
  String languageCode = 'es',
}) {
  final parsed = parseIsoDate(iso);
  if (parsed == null) return iso;
  final lang = languageCode.toLowerCase();
  if (lang.startsWith('es')) {
    final d = parsed.day.toString().padLeft(2, '0');
    final m = parsed.month.toString().padLeft(2, '0');
    return '$d/$m/${parsed.year}';
  }
  return iso;
}