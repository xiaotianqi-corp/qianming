export default function SignatureIndex({ plans, countries }) {
    const [country, setCountry] = useState(countries[0]);
  
    return (
      <>
        <CountrySelector value={country} onChange={setCountry} />
  
        {plans.map(plan => (
          <PlanCard
            key={plan.code}
            plan={plan}
            onSelect={() => post('/products/signature/orders', {
              product: plan.code,
              country: country.code
            })}
          />
        ))}
      </>
    );
  }
  