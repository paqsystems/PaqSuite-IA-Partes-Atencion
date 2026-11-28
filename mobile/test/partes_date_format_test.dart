import 'package:flutter_test/flutter_test.dart';
import 'package:partes_atencion_mobile/features/partes/partes_date_format.dart';

void main() {
  test('formatIsoDateForDisplay es dd/MM/yyyy en español', () {
    expect(
      formatIsoDateForDisplay('2026-11-01', languageCode: 'es'),
      '01/11/2026',
    );
  });

  test('currentCalendarMonthRangeIso cubre el mes actual', () {
    final range = currentCalendarMonthRangeIso(DateTime(2026, 11, 15));
    expect(range.fechaDesde, '2026-11-01');
    expect(range.fechaHasta, '2026-11-30');
  });
}