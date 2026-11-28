import 'package:flutter_test/flutter_test.dart';
import 'package:partes_atencion_mobile/features/partes/partes_date_format.dart'
    show isoFromDateTime, parseIsoDate, todayIsoDate;
import 'package:partes_atencion_mobile/features/partes/partes_duration.dart';

void main() {
  test('isValidDuracionMinutos respeta tramo', () {
    expect(isValidDuracionMinutos(30, 15), isTrue);
    expect(isValidDuracionMinutos(25, 15), isFalse);
    expect(isValidDuracionMinutos(0, 15), isFalse);
  });

  test('buildTramoOptions genera múltiplos', () {
    expect(buildTramoOptions(15, maxMinutos: 45), [15, 30, 45]);
  });

  test('isFechaFuturaIso', () {
    final hoy = todayIsoDate();
    expect(isFechaFuturaIso(hoy), isFalse);
    final parsed = parseIsoDate(hoy)!;
    final manana = DateTime(parsed.year, parsed.month, parsed.day + 1);
    expect(isFechaFuturaIso(isoFromDateTime(manana)), isTrue);
  });
}