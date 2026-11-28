import 'package:flutter/material.dart';

import '../partes_date_format.dart';

/// Campo de fecha con picker; muestra dd/MM/yyyy y guarda ISO para la API.
class PartesDateField extends StatelessWidget {
  const PartesDateField({
    super.key,
    required this.label,
    required this.isoValue,
    required this.onIsoChanged,
    this.enabled = true,
    this.fieldKey,
  });

  final String label;
  final String isoValue;
  final ValueChanged<String> onIsoChanged;
  final bool enabled;
  final Key? fieldKey;

  Future<void> _pick(BuildContext context) async {
    final locale = Localizations.localeOf(context);
    final initial = parseIsoDate(isoValue) ?? DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: initial,
      firstDate: DateTime(2000),
      lastDate: DateTime(2100),
      locale: locale.languageCode.startsWith('es')
          ? const Locale('es', 'AR')
          : locale,
    );
    if (picked != null) {
      onIsoChanged(isoFromDateTime(picked));
    }
  }

  @override
  Widget build(BuildContext context) {
    final locale = Localizations.localeOf(context);
    final display =
        formatIsoDateForDisplay(isoValue, languageCode: locale.languageCode);

    return FormField<String>(
      key: fieldKey,
      builder: (state) {
        return InkWell(
          onTap: enabled ? () => _pick(context) : null,
          child: InputDecorator(
            decoration: InputDecoration(
              labelText: label,
              suffixIcon: const Icon(Icons.calendar_today_outlined),
            ),
            child: Text(display),
          ),
        );
      },
    );
  }
}