#!/usr/bin/env python3
"""
CUA CoursePlanner Hallucination Test
Uses Claude Opus 4.7 as both the advisor and the evaluator judge.
Each test case has a known ground-truth answer from the system prompt.
"""

import os
import json
import anthropic

MODEL = "claude-opus-4-7"
client = anthropic.Anthropic()

# Load the system prompt from the project
SYSTEM_PROMPT_PATH = os.path.join(os.path.dirname(__file__), "storage/app/system_prompt.txt")
with open(SYSTEM_PROMPT_PATH) as f:
    BASE_SYSTEM_PROMPT = f.read()

FORMATTING_RULE = (
    "\n\nFORMATTING RULE: Never use markdown bold formatting (** **) in your responses. "
    "Use plain text, dashes, or numbered lists only. Be concise — 3 to 5 sentences for "
    "general questions. For any course or semester recommendation, always provide a complete "
    "list totaling 15-17 credits — do not cut the list short for brevity. Never repeat "
    "information already shown in the student profile.\n\n"
    "PROFILE DATA RULE: Your context contains STUDENT profile data, COMPLETED courses, "
    "IN_PROGRESS courses, and REMAINING requirements for this specific student. You MUST use "
    "this data exclusively. Never recommend any course listed in COMPLETED or IN_PROGRESS — "
    "those are already taken or in progress. Never guess at requirements — read them from the "
    "REMAINING lines. If the student asks what they still need, answer only from the REMAINING "
    "context. Never ask the student for information already present in the STUDENT profile line."
)


# ---------------------------------------------------------------------------
# Test cases — each has:
#   scenario:   student profile context injected into system prompt
#   question:   the user message sent to the advisor
#   ground_truth: the factual answer(s) that MUST appear / must NOT appear
#   must_include: list of strings the response should contain (case-insensitive)
#   must_exclude: list of strings that would indicate a hallucination
# ---------------------------------------------------------------------------
TEST_CASES = [
    {
        "id": "T01",
        "name": "Pre-2024 Data Analytics requirements",
        "scenario": (
            "DATE: Jun 13, 2026 | SEM: Summer 2026 | NEXT: Fall 2026\n"
            "STUDENT: Alice Chen | B.S.B.A. | Pre-2024 | Admit: Fall 2023 | "
            "Standing: sophomore | Credits: 45 | Grad: Spring 2027 | Specs: data_analytics_for_business\n"
            "COMPLETED: SRES 101, ACCT 205, ENT 118B, MGT 123B, ENG 101\n"
            "IN_PROGRESS: None\n"
            "REMAINING: MGT 331 (required for Data Analytics), MGT 351, MGT 361, DA 314"
        ),
        "question": "What courses do I still need for my Data Analytics specialization?",
        "must_include": ["MGT 331", "MGT 351", "MGT 361", "DA 314"],
        "must_exclude": ["DA 124", "DA 401", "DA 423"],
        "rationale": "Pre-2024 Data Analytics requires all 4: DA 314, MGT 331, MGT 351, MGT 361. "
                     "DA 124/401/423 are Post-2024 only.",
    },
    {
        "id": "T02",
        "name": "Sports Management double-spec requirement",
        "scenario": (
            "DATE: Jun 13, 2026 | SEM: Summer 2026 | NEXT: Fall 2026\n"
            "STUDENT: Bob Rivera | B.S.B.A. | Post-2024 | Admit: Fall 2024 | "
            "Standing: freshman | Credits: 15 | Grad: Spring 2028 | Specs: sports_management\n"
            "COMPLETED: ENT 118A, SRES 101\n"
            "IN_PROGRESS: None\n"
            "REMAINING: MGT 311, MGT 324, MGT 327, MKT 325, [2nd specialization required]"
        ),
        "question": "Can I just do Sports Management as my only specialization?",
        "must_include": ["double", "second specialization"],
        "must_exclude": [],
        "rationale": "Sports Management REQUIRES double specialization — the bot must flag this.",
    },
    {
        "id": "T03",
        "name": "MGT 475 and BUS 498 must be same semester",
        "scenario": (
            "DATE: Jun 13, 2026 | SEM: Summer 2026 | NEXT: Fall 2026\n"
            "STUDENT: Carol Nguyen | B.S.B.A. | Post-2024 | Admit: Fall 2022 | "
            "Standing: senior | Credits: 92 | Grad: Spring 2027 | Specs: marketing\n"
            "COMPLETED: ACCT 205, ACCT 206, FIN 226, MKT 345, MGT 123B\n"
            "IN_PROGRESS: None\n"
            "REMAINING: MGT 475, BUS 498, MKT 346, MKT 348, MKT 457"
        ),
        "question": "Can I take MGT 475 this fall and save BUS 498 for spring to lighten my load?",
        "must_include": ["same semester", "same"],
        "must_exclude": [],
        "rationale": "MGT 475 and BUS 498 MUST be taken the SAME semester for BSBA students.",
    },
    {
        "id": "T04",
        "name": "Finance specialization requires MATH 111",
        "scenario": (
            "DATE: Jun 13, 2026 | SEM: Summer 2026 | NEXT: Fall 2026\n"
            "STUDENT: David Kim | B.S.B.A. | Post-2024 | Admit: Fall 2024 | "
            "Standing: freshman | Credits: 18 | Grad: Spring 2028 | Specs: finance\n"
            "COMPLETED: SRES 101, ENT 118A, MGT 123B\n"
            "IN_PROGRESS: None\n"
            "REMAINING: FIN 332, FIN 334, FIN 436, FIN 227, [1 elective], MATH 111 (required for Finance)"
        ),
        "question": "Do I need any specific math for Finance?",
        "must_include": ["MATH 111"],
        "must_exclude": ["MATH 110 is enough", "no math requirement"],
        "rationale": "Finance specialization explicitly requires MATH 111.",
    },
    {
        "id": "T05",
        "name": "SPAN 111 satisfies both Language I and II",
        "scenario": (
            "DATE: Jun 13, 2026 | SEM: Summer 2026 | NEXT: Fall 2026\n"
            "STUDENT: Emma Patel | B.S.B.A. | Post-2024 | Admit: Fall 2025 | "
            "Standing: freshman | Credits: 12 | Grad: Spring 2029 | Specs: marketing\n"
            "COMPLETED: ENT 118A\n"
            "IN_PROGRESS: None\n"
            "REMAINING: Language I, Language II, [many others]"
        ),
        "question": "A friend told me SPAN 111 counts for both Language I and Language II. Is that true?",
        "must_include": ["both", "SPAN 111"],
        "must_exclude": ["still need language ii", "must also take", "does not satisfy both"],
        "rationale": "SPAN 111 (or 113) satisfies BOTH Language I and Language II in one course.",
    },
    {
        "id": "T06",
        "name": "Never recommend completed course",
        "scenario": (
            "DATE: Jun 13, 2026 | SEM: Summer 2026 | NEXT: Fall 2026\n"
            "STUDENT: Frank Torres | B.S.B.A. | Post-2024 | Admit: Fall 2023 | "
            "Standing: junior | Credits: 67 | Grad: Spring 2025 | Specs: marketing\n"
            "COMPLETED: ACCT 205, ACCT 206, FIN 226, MKT 345, SRES 101, SRES 102, ENT 118B, "
            "MGT 123B, ENG 101, MGT 250, BUS 299A, BUS 399A\n"
            "IN_PROGRESS: MKT 346\n"
            "REMAINING: MKT 348, MKT 457, [1 MKT elective], MGT 475, BUS 498, BUS 499A, "
            "Liberal Arts courses, SRES 290"
        ),
        "question": "What should I take next fall to stay on track?",
        "must_include": ["MKT 348", "MGT 475"],
        "must_exclude": ["ACCT 205", "ACCT 206", "FIN 226", "MKT 345", "MKT 346"],
        "rationale": "Bot must never recommend courses already completed or in progress.",
    },
    {
        "id": "T07",
        "name": "BUS 299A credit gate — 24 credits required",
        "scenario": (
            "DATE: Jun 13, 2026 | SEM: Summer 2026 | NEXT: Fall 2026\n"
            "STUDENT: Grace Liu | B.S.B.A. | Post-2024 | Admit: Fall 2025 | "
            "Standing: freshman | Credits: 18 | Grad: Spring 2029 | Specs: marketing\n"
            "COMPLETED: ENT 118A, SRES 101, ENG 101\n"
            "IN_PROGRESS: None\n"
            "REMAINING: BUS 199 (by 30cr), BUS 299A (need 24cr — currently 18cr so not yet eligible)"
        ),
        "question": "Can I take BUS 299A this coming fall semester?",
        "must_include": ["24", "credits"],
        "must_exclude": ["yes, you can take BUS 299A", "you're eligible for BUS 299A"],
        "rationale": "BUS 299A gate is 24 credits; student only has 18, so they cannot take it yet.",
    },
    {
        "id": "T08",
        "name": "BS Accounting students do NOT declare specializations",
        "scenario": (
            "DATE: Jun 13, 2026 | SEM: Summer 2026 | NEXT: Fall 2026\n"
            "STUDENT: Henry Park | B.S. Accounting | Post-2024 | Admit: Fall 2024 | "
            "Standing: sophomore | Credits: 35 | Grad: Spring 2028 | Specs: None\n"
            "COMPLETED: ACCT 205, SRES 101, ENT 118B, MGT 123B, ENG 101\n"
            "IN_PROGRESS: ACCT 206\n"
            "REMAINING: ACCT 310, ACCT 311, ACCT 315, ACCT 412, ACCT 417, ACCT 418, ACCT 419, ACCT 422, "
            "ACCT 442, MGT 265, FIN 226, MKT 345, MGT 250, MGT 475, ACCT 498"
        ),
        "question": "I want to add a Finance specialization to my BS Accounting. How do I do that?",
        "must_include": ["do not", "cannot", "not declare", "separately", "double-declare"],
        "must_exclude": ["you can add finance", "declare finance specialization", "finance specialization to your bs accounting"],
        "rationale": "BS Accounting students do NOT declare specializations. Must double-declare separately as BSBA.",
    },
    {
        "id": "T09",
        "name": "ACCT 310 is Fall only — missing it delays a year",
        "scenario": (
            "DATE: Jun 13, 2026 | SEM: Summer 2026 | NEXT: Fall 2026\n"
            "STUDENT: Iris Yoon | B.S. Accounting | Pre-2024 | Admit: Fall 2023 | "
            "Standing: junior | Credits: 62 | Grad: Spring 2027 | Specs: None\n"
            "COMPLETED: ACCT 205, ACCT 206, FIN 226, SRES 101, SRES 102, ENT 118B, MGT 123B\n"
            "IN_PROGRESS: None\n"
            "REMAINING: ACCT 310 (Fall only), ACCT 311 (Spring only), ACCT 312, ACCT 412, "
            "ACCT 417, ACCT 418, ACCT 419, ACCT 422, ACCT 442, ACCT 480, MGT 365, MGT 475, ACCT 498"
        ),
        "question": "What happens if I don't take ACCT 310 this coming fall?",
        "must_include": ["fall", "delay", "year"],
        "must_exclude": [],
        "rationale": "ACCT 310 is Fall only; missing it forces student to wait until next fall, delaying the whole chain.",
    },
    {
        "id": "T10",
        "name": "Post-2024 Marketing has 2 electives, not 1",
        "scenario": (
            "DATE: Jun 13, 2026 | SEM: Summer 2026 | NEXT: Fall 2026\n"
            "STUDENT: Jake Moore | B.S.B.A. | Post-2024 | Admit: Fall 2023 | "
            "Standing: junior | Credits: 65 | Grad: Spring 2026 | Specs: marketing\n"
            "COMPLETED: ACCT 205, ACCT 206, FIN 226, MKT 345, SRES 101, SRES 102, ENT 118B, MGT 123B\n"
            "IN_PROGRESS: MKT 346\n"
            "REMAINING: MKT 348, MKT 457, [2 MKT electives — Post-2024], SRES 290, MGT 475, BUS 498"
        ),
        "question": "How many electives do I need for my Marketing specialization?",
        "must_include": ["2", "two"],
        "must_exclude": ["1 elective", "one elective", "only one"],
        "rationale": "Post-2024 Marketing requires 2 electives (vs Pre-2024's 1).",
    },
]


def run_advisor(scenario: str, question: str) -> str:
    """Run the question through the advisor (Claude Opus 4.7 playing the CUA advising bot)."""
    full_system = BASE_SYSTEM_PROMPT + FORMATTING_RULE + "\n\n" + scenario
    resp = client.messages.create(
        model=MODEL,
        max_tokens=600,
        system=full_system,
        messages=[{"role": "user", "content": question}],
    )
    return resp.content[0].text


def evaluate_response(test: dict, advisor_response: str) -> dict:
    """Use Claude Opus 4.7 as a judge to score the advisor response."""
    judge_prompt = f"""You are a strict factual evaluator for a university academic advising chatbot.

GROUND TRUTH (sourced from official curriculum documents):
{test['rationale']}

STRINGS THE RESPONSE MUST INCLUDE (case-insensitive, at least one per group):
{json.dumps(test['must_include'])}

STRINGS THAT INDICATE A HALLUCINATION (must NOT appear):
{json.dumps(test['must_exclude'])}

ADVISOR RESPONSE TO EVALUATE:
{advisor_response}

Evaluate whether the advisor response:
1. Correctly addresses the factual ground truth
2. Includes the required strings/concepts
3. Does NOT contain any hallucination indicators

Respond in this exact JSON format:
{{
  "pass": true or false,
  "must_include_found": ["list of required strings actually found in response"],
  "must_include_missing": ["list of required strings NOT found"],
  "hallucinations_found": ["list of prohibited strings found — empty if none"],
  "verdict": "one sentence explaining pass or fail"
}}"""

    resp = client.messages.create(
        model=MODEL,
        max_tokens=400,
        messages=[{"role": "user", "content": judge_prompt}],
    )
    raw = resp.content[0].text.strip()
    # Extract JSON block if wrapped in markdown
    if "```" in raw:
        raw = raw.split("```")[1].lstrip("json").strip()
    return json.loads(raw)


def main():
    print("=" * 70)
    print(f"CUA CoursePlanner Hallucination Test  |  Model: {MODEL}")
    print("=" * 70)
    print()

    results = []
    passed = 0
    failed = 0

    for test in TEST_CASES:
        print(f"[{test['id']}] {test['name']}")
        print(f"  Q: {test['question'][:80]}...")

        advisor_response = run_advisor(test["scenario"], test["question"])

        eval_result = evaluate_response(test, advisor_response)
        verdict = "PASS" if eval_result["pass"] else "FAIL"
        if eval_result["pass"]:
            passed += 1
        else:
            failed += 1

        print(f"  Result: {verdict}")
        print(f"  Verdict: {eval_result['verdict']}")

        if eval_result.get("must_include_missing"):
            print(f"  Missing: {eval_result['must_include_missing']}")
        if eval_result.get("hallucinations_found"):
            print(f"  Hallucinations: {eval_result['hallucinations_found']}")

        if verdict == "FAIL":
            print(f"  Advisor said: {advisor_response[:300]}...")

        print()
        results.append({**test, "advisor_response": advisor_response, "evaluation": eval_result})

    print("=" * 70)
    print(f"FINAL SCORE: {passed}/{len(TEST_CASES)} passed  |  {failed} failed")
    print("=" * 70)

    if failed > 0:
        print("\nFAILED TESTS:")
        for r in results:
            if not r["evaluation"]["pass"]:
                print(f"  [{r['id']}] {r['name']}")
                print(f"       {r['evaluation']['verdict']}")

    # Save full report
    report_path = os.path.join(os.path.dirname(__file__), "hallucination_report.json")
    with open(report_path, "w") as f:
        json.dump(results, f, indent=2)
    print(f"\nFull report saved to: {report_path}")


if __name__ == "__main__":
    main()
