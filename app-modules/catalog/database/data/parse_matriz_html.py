#!/usr/bin/env python3
"""
Converte o HTML da tela "Consulta Curso e Matriz Curricular" do portal da UTFPR
no CSV que o MatrizUtfprSeeder consome.

Por que existe: transcrever matriz à mão a partir do PDF é lento e frágil — e a
coluna "Equivalentes" sai truncada na margem do PDF. O HTML tem tudo, exato.

Como obter o HTML (a página fica dentro de um iframe, então Cmd+S salva a casca
errada): DevTools → Elements → localizar a <table id="grade"> → botão direito →
Copy → Copy outerHTML.

    python3 parse_matriz_html.py ../../../../docs_referencia/matriz_html.html

Gera, ao lado deste arquivo:
    matriz-45-utfpr-fb.csv          disciplinas da matriz
    equivalencias-45-utfpr-fb.csv   disciplinas equivalentes (mudança de matriz)
"""
from __future__ import annotations

import csv
import html
import re
import sys
from pathlib import Path

HERE = Path(__file__).parent


def cells(row_html: str) -> list[str]:
    """Texto limpo de cada <td> da linha, preservando <br> como separador."""
    out = []
    for raw in re.findall(r"<td\b[^>]*>(.*?)</td>", row_html, re.S | re.I):
        # <br> separa itens numa mesma célula (equivalentes, pré-requisitos)
        txt = re.sub(r"<br\s*/?>", "\n", raw, flags=re.I)
        txt = re.sub(r"<hr\s*/?>", "\n", txt, flags=re.I)
        # "Turmas" é um link de navegação, não dado
        txt = re.sub(r"<a[^>]*onclick=[^>]*>.*?</a>", "", txt, flags=re.S | re.I)
        txt = re.sub(r"<[^>]+>", "", txt)
        txt = html.unescape(txt).replace("\xa0", " ")
        lines = [ln.strip() for ln in txt.split("\n")]
        out.append("\n".join(ln for ln in lines if ln))
    return out


def number(cell: str) -> int:
    m = re.search(r"-?\d+", cell.replace(".", ""))
    return int(m.group()) if m else 0


def main(path: Path) -> None:
    source = path.read_text(encoding="utf-8")
    body = source[source.index("<tbody"):] if "<tbody" in source else source
    rows = re.findall(r"<tr\b[^>]*>(.*?)</tr>", body, re.S | re.I)

    subjects: list[dict] = []
    equivalences: list[dict] = []

    for row in rows:
        c = cells(row)
        if len(c) < 15:
            continue                      # linha de cabeçalho ou separador
        code = c[2].split("\n")[0].strip()
        if not re.fullmatch(r"[A-Z0-9 ]{3,12}", code):
            continue

        prereq_raw = c[14]
        prereqs: list[str] = []
        for line in prereq_raw.split("\n"):
            line = line.strip()
            if not line:
                continue
            if m := re.match(r"Per[íi]odo\s*:\s*(\d+)", line, re.I):
                prereqs.append(f"PERIODO:{m.group(1)}")
            else:
                for token in re.findall(r"\b[A-Z]{2,4}\d{2,4}[A-Z]?\b", line):
                    prereqs.append(token)

        subjects.append({
            "periodo": number(c[0]),
            "grupo": (re.search(r"\d+", c[1]).group() if re.search(r"\d+", c[1]) else ""),
            "codigo": code.replace(" ", ""),
            "nome": c[3],
            "modelo": c[4],
            "chs": number(c[7]),      # Total de aulas semanais
            "cht": number(c[13]),     # Carga horária total ("60 horas")
            "chext": number(c[11]),   # Total de horas de CHEXT
            "prereq": "+".join(dict.fromkeys(prereqs)),
        })

        # Equivalentes: três colunas paralelas (disciplina, CHT, grupo), uma
        # linha cada dentro da célula.
        if len(c) >= 18:
            eq_codes = [x for x in c[15].split("\n") if x.strip()]
            eq_hours = [x for x in c[16].split("\n") if x.strip()]
            eq_group = [x for x in c[17].split("\n") if x.strip()]
            for i, eq in enumerate(eq_codes):
                equivalences.append({
                    "codigo": code.replace(" ", ""),
                    "equivalente": eq.strip(),
                    "cht": number(eq_hours[i]) if i < len(eq_hours) else 0,
                    "grupo": (eq_group[i].strip() if i < len(eq_group) else ""),
                })

    header = (
        "# Matriz 45 - Sistemas De Informação 02 · UTFPR · Câmpus Francisco Beltrão\n"
        "# GERADO por parse_matriz_html.py a partir do HTML da tela do portal.\n"
        "# NÃO edite à mão: rode o script de novo quando a matriz mudar.\n"
        "#\n"
        "# prereq: códigos separados por \"+\" (todos exigidos) · \"PERIODO:N\" para período mínimo\n"
    )
    out = HERE / "matriz-45-utfpr-fb.csv"
    with out.open("w", encoding="utf-8", newline="") as f:
        f.write(header)
        w = csv.DictWriter(f, fieldnames=list(subjects[0].keys()))
        w.writeheader()
        w.writerows(subjects)

    out_eq = HERE / "equivalencias-45-utfpr-fb.csv"
    with out_eq.open("w", encoding="utf-8", newline="") as f:
        f.write("# Disciplinas equivalentes — a origem do \"Crédito Consignado\"\n"
                "# do histórico (mudança de matriz). GERADO por parse_matriz_html.py.\n")
        w = csv.DictWriter(f, fieldnames=["codigo", "equivalente", "cht", "grupo"])
        w.writeheader()
        w.writerows(equivalences)

    obr = [s for s in subjects if not s["grupo"]]
    opt = [s for s in subjects if s["grupo"]]
    print(f"disciplinas: {len(subjects)}  ({len(obr)} obrigatórias, {len(opt)} optativas)")
    print(f"  CHT obrigatórias  : {sum(s['cht'] for s in obr)}   (esperado 2730)")
    print(f"  CHEXT obrigatórias: {sum(s['chext'] for s in obr)}   (esperado 240)")
    print(f"  CHT optativas     : {sum(s['cht'] for s in opt)}")
    print(f"  arestas de pré-req: {sum(len(s['prereq'].split('+')) for s in subjects if s['prereq'])}")
    print(f"  equivalências     : {len(equivalences)}")


if __name__ == "__main__":
    main(Path(sys.argv[1]))
