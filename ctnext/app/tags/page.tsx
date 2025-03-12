import { TermTag } from "@/components/terms/term--tag"
import { drupal } from "@/lib/drupal"
import type { Metadata } from "next"
import type { DrupalTaxonomyTerm } from "next-drupal"

export const metadata: Metadata = {
  description: "A Next.js site powered by a Drupal backend.",
}

export default async function TagTerms() {
  const terms = await drupal.getResourceCollection<DrupalTaxonomyTerm[]>(
    "taxonomy_term--tags",
    {
      params: {
        "filter[status]": 1,
        "fields[taxonomy_term--tags]": "name,description"
      },
      next: {
        revalidate: 0,
      },
    }
  )

  console.log(terms)

  return (
    <>
      <h1 className="mb-10 text-6xl font-black">Tags:</h1>
      {terms?.length ? (
        terms.map((term: DrupalTaxonomyTerm) => {
          console.log(term)
          return (
            <div key={term.id}>
              <TermTag term={term} />
              <hr className="my-2.5" />
            </div>
          )
        })
      ) : (
        <p className="py-4">No terms found</p>
      )}
    </>
  )
}
