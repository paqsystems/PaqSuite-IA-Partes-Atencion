import 'package:flutter_test/flutter_test.dart';
import 'package:partes_atencion_mobile/features/partes/partes_paquete_aggregate.dart';

void main() {
  test('aggregatePaqueteHorasDesglose agrupa por cliente', () {
    final rows = [
      {
        'clienteCode': 'C01',
        'clienteNombre': 'Demo',
        'esTarea': true,
        'duracionMinutos': 60,
      },
      {
        'clienteCode': 'C01',
        'clienteNombre': 'Demo',
        'esTarea': false,
        'duracionMinutos': 15,
      },
    ];
    final result = aggregatePaqueteHorasDesglose(rows, 'cliente');
    expect(result.length, 1);
    expect(result.first.totalMinutos, 45);
  });
}